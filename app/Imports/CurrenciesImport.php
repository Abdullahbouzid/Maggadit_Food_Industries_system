<?php

namespace App\Imports;

use App\Models\Currency;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class CurrenciesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    protected $errors = [];

    public function model(array $row)
    {
        return new Currency([
            'name'             => $row['name'],
            'code'             => $row['code'],
            'coin_description' => $row['coin_description'] ?? null,
            'base_currency'    => filter_var($row['base_currency'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:currencies,name',
            'code' => 'required|string|max:10|unique:currencies,code',
            'coin_description' => 'nullable|string|max:255',
            'base_currency' => 'nullable|boolean',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}