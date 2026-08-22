<?php

// app/Exports/UomsExport.php

namespace App\Exports;

use App\Models\Uom;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UomsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Uom::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('uom_symbol', 'like', "%{$search}%");
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
            'ID', 'Code', 'Name (English)', 'Name (Arabic)', 'UOM Symbol', 'Description', 'Is Active', 'Sort Order', 'Image Path', 'Created At',
        ];
    }

    public function map($uom): array
    {
        return [
            $uom->id,
            $uom->code,
            $uom->name_en,
            $uom->name_ar,
            $uom->uom_symbol,
            $uom->description,
            $uom->is_active ? 'Active' : 'Inactive',
            $uom->sort_order,
            $uom->image,
            $uom->created_at,
        ];
    }
}
