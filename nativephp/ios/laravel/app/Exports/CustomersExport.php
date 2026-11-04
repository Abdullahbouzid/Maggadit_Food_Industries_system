<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Customer::query();

        // تطبيق الفلاتر (نفس فلاتر صفحة الفهرس)
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // يمكن إضافة فلاتر إضافية حسب الحاجة، مثلاً حسب المنظمة النشطة
        if ($this->request->filled('organization_id')) {
            $query->where('active_organization_id', $this->request->organization_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Username',
            'Email',
            'Active Organization',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->username,
            $customer->email,
            $customer->activeOrganization ? $customer->activeOrganization->name : '—',
            $customer->created_at ? $customer->created_at->format('Y-m-d H:i') : '',
        ];
    }
}
