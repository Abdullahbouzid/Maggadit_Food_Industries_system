<?php

namespace App\Exports;

use App\Models\ItemModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemModelsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = ItemModel::query();
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
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

    public function map($model): array
    {
        return [$model->id, $model->code, $model->name_en, $model->name_ar, $model->is_active ? 'Active' : 'Inactive', $model->sort_order, $model->created_at];
    }
}
