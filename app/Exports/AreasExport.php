<?php

namespace App\Exports;

use App\Models\Area;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class AreasExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Area::with('state');

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

        if ($this->request->filled('state_id')) {
            $query->where('state_id', $this->request->state_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Code', 'Name (EN)', 'Name (AR)', 'Description', 'State ID', 'State Name', 'Active', 'Sort Order', 'Created At'
        ];
    }

    public function map($area): array
    {
        return [
            $area->id,
            $area->code,
            $area->name_en,
            $area->name_ar,
            $area->description,
            $area->state_id,
            $area->state?->name_en,
            $area->is_active ? 'Yes' : 'No',
            $area->sort_order,
            $area->created_at,
        ];
    }
}