<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements SkipsOnError, ToModel, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    protected $errors = [];

    public function model(array $row)
    {
        // إذا كان العمود 'password' فارغاً، نضع كلمة مرور افتراضية
        $password = isset($row['password']) && ! empty($row['password'])
            ? Hash::make($row['password'])
            : Hash::make('password123'); // أو يمكن توليد عشوائي

        // البحث عن المنظمة عبر الاسم (إذا ورد)
        $organizationId = null;
        if (! empty($row['active_organization_name'])) {
            $org = Organization::where('name', $row['active_organization_name'])->first();
            if ($org) {
                $organizationId = $org->id;
            } else {
                // نضيف خطأ في السجل
                $this->errors[] = "المنظمة '{$row['active_organization_name']}' غير موجودة في الصف رقم ".($this->rowNumber ?? 'غير معروف');
            }
        }

        $customer = Customer::create([
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'password' => $password,
            'active_organization_id' => $organizationId,
        ]);

        // إذا تم تحديد منظمة، نربطها في الجدول الوسيط
        if ($organizationId) {
            $customer->organizations()->attach($organizationId, ['is_active' => true]);
        }

        return $customer;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customers,username',
            'email' => 'required|email|max:255|unique:customers,email',
            'password' => 'nullable|string|min:8', // اختياري في الاستيراد
            'active_organization_name' => 'nullable|string|max:255',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
