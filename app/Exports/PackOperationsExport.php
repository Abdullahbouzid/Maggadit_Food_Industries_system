<?php
// app/Exports/PackOperationsExport.php

namespace App\Exports;

use App\Models\PackOperation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class PackOperationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = PackOperation::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('status')) {
            if ($this->request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($this->request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('name_en')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Code', 'Name (English)', 'Name (Arabic)', 'Is Active', 'Sort Order', 'Created At'
        ];
    }

    public function map($packOp): array
    {
        return [
            $packOp->id,
            $packOp->code,
            $packOp->name_en,
            $packOp->name_ar,
            $packOp->is_active ? 'Active' : 'Inactive',
            $packOp->sort_order,
            $packOp->created_at,
        ];
    }
}