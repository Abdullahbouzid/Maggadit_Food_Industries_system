<?php
// app/Exports/PackGroupsExport.php

namespace App\Exports;

use App\Models\PackGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class PackGroupsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = PackGroup::query();
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
        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Code', 'Name (EN)', 'Name (AR)', 'Is Active', 'Sort Order', 'Created At'];
    }

    public function map($group): array
    {
        return [
            $group->id,
            $group->code,
            $group->name_en,
            $group->name_ar,
            $group->is_active ? 'Active' : 'Inactive',
            $group->sort_order,
            $group->created_at,
        ];
    }
}