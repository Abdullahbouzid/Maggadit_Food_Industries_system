<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class DevicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Device::with('organization');

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('serial', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('organization_id')) {
            $query->where('organization_id', $this->request->organization_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Name (EN)', 'Name (AR)', 'Serial', 'Organization',
            'Model No', 'Brand', 'Remark', 'Purchase Date',
            'Warranty Expiry', 'Inactive', 'Created At'
        ];
    }

    public function map($device): array
    {
        return [
            $device->id,
            $device->name_en,
            $device->name_ar,
            $device->serial,
            $device->organization?->name,
            $device->model_no,
            $device->brand,
            $device->remark,
            $device->purchase_date,
            $device->warranty_expiry,
            $device->is_inactive ? 'Yes' : 'No',
            $device->created_at,
        ];
    }
}