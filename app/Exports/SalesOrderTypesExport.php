<?php
// app/Exports/SalesOrderTypesExport.php

namespace App\Exports;

use App\Models\SalesOrderType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class SalesOrderTypesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = SalesOrderType::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('active_status')) {
            if ($this->request->active_status === 'active') {
                $query->where('active', true);
            } elseif ($this->request->active_status === 'inactive') {
                $query->where('active', false);
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Code', 'Active', 'Default', 'Created At'
        ];
    }

    public function map($type): array
    {
        return [
            $type->id,
            $type->name,
            $type->code,
            $type->active ? 'Yes' : 'No',
            $type->default ? 'Yes' : 'No',
            $type->created_at,
        ];
    }
}