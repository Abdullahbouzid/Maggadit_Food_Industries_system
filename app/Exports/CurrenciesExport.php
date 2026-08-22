<?php
// app/Exports/CurrenciesExport.php

namespace App\Exports;

use App\Models\Currency;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class CurrenciesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Currency::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('coin_description', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('base_currency_filter')) {
            $query->where('base_currency', $this->request->base_currency_filter == 'yes');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Code', 'Coin Description', 'Base Currency', 'Created At'
        ];
    }

    public function map($currency): array
    {
        return [
            $currency->id,
            $currency->name,
            $currency->code,
            $currency->coin_description ?? '',
            $currency->base_currency ? 'Yes' : 'No',
            $currency->created_at,
        ];
    }
}