<?php

// app/Exports/ItemTypesExport.php

namespace App\Exports;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemTypesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = ItemType::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('status')) {
            if ($this->request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($this->request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Code', 'Name', 'Description', 'Is Active', 'Sort Order', 'Image Path', 'Created At',
        ];
    }

    public function map($itemType): array
    {
        return [
            $itemType->id,
            $itemType->code,
            $itemType->name,
            $itemType->description,
            $itemType->is_active ? 'Active' : 'Inactive',
            $itemType->sort_order,
            $itemType->image,
            $itemType->created_at,
        ];
    }
}
