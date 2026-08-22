<?php

namespace App\Exports;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarehousesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Warehouse::with(['warehouseType', 'organization', 'country', 'state', 'city', 'area']);

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('warehouse_type_id')) {
            $query->where('warehouse_type_id', $this->request->warehouse_type_id);
        }

        if ($this->request->filled('organization_id')) {
            $query->where('organization_id', $this->request->organization_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Code', 'Name EN', 'Name AR', 'Warehouse Type', 'Organization',
            'Phone', 'Fax', 'Barcode', 'Address EN', 'Address AR',
            'GPS Latitude', 'GPS Longitude', 'Zip Code', 'Country', 'State', 'City', 'Area', 'Street',
            'Destinations', 'Sources', 'Employees', 'Active', 'Sort Order',
        ];
    }

    public function map($warehouse): array
    {
        return [
            $warehouse->id,
            $warehouse->code,
            $warehouse->name_en,
            $warehouse->name_ar,
            $warehouse->warehouseType?->name_en,
            $warehouse->organization?->name,
            $warehouse->phone,
            $warehouse->fax,
            $warehouse->barcode,
            $warehouse->address_en,
            $warehouse->address_ar,
            $warehouse->gps_latitude,
            $warehouse->gps_longitude,
            $warehouse->zip_code,
            $warehouse->country?->name,
            $warehouse->state?->name_en,
            $warehouse->city?->name,
            $warehouse->area?->name_en,
            $warehouse->street,
            is_array($warehouse->destinations) ? implode(',', $warehouse->destinations) : $warehouse->destinations,
            is_array($warehouse->sources) ? implode(',', $warehouse->sources) : $warehouse->sources,
            is_array($warehouse->employees) ? implode(',', $warehouse->employees) : $warehouse->employees,
            $warehouse->is_active ? 'Yes' : 'No',
            $warehouse->sort_order,
        ];
    }
}
