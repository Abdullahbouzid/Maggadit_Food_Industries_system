<?php

namespace App\Exports;

use App\Models\WarehouseType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class WarehouseTypesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = WarehouseType::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('status')) {
            $query->where('is_active', $this->request->status == 'active');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Code', 'Name (EN)', 'Name (AR)', 'Description', 'Active', 'Sort Order', 'Created At'
        ];
    }

    public function map($type): array
    {
        return [
            $type->id,
            $type->code,
            $type->name_en,
            $type->name_ar,
            $type->description,
            $type->is_active ? 'Yes' : 'No',
            $type->sort_order,
            $type->created_at,
        ];
    }
}