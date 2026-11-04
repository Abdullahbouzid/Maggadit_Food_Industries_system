<?php

// app/Imports/CompetitorsImport.php

namespace App\Imports;

use App\Models\Competitor;
use App\Models\Organization;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class CompetitorsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    protected $errors = [];

    public function model(array $row)
    {
        // التحقق من وجود organization_id صحيح
        if (! empty($row['organization_id']) && ! Organization::where('id', $row['organization_id'])->exists()) {
            $this->errors[] = "المعرف {$row['organization_id']} غير موجود في جدول المنظمات (الصف: {$row['name']})";

            return null;
        }

        return new Competitor([
            'name' => $row['name'],
            'code' => $row['code'],
            'organization_id' => $row['organization_id'] ?? null,
            'competitor_type' => $row['competitor_type'] ?? null,
            'gln' => $row['gln'] ?? null,
            'contact_person' => $row['contact_person'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:competitors,code',
            'gln' => 'nullable|string|unique:competitors,gln',
            'email' => 'nullable|email',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "صف {$failure->row()}: ".implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
