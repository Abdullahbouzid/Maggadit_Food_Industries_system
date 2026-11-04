<?php

namespace App\Exports;

use App\Models\State;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StatesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = State::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
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
            'ID', 'Code', 'Name (EN)', 'Name (AR)', 'Description', 'Active', 'Sort Order', 'Created At',
        ];
    }

    public function map($state): array
    {
        return [
            $state->id,
            $state->code,
            $state->name_en,
            $state->name_ar,
            $state->description,
            $state->is_active ? 'Yes' : 'No',
            $state->sort_order,
            $state->created_at,
        ];
    }
}
