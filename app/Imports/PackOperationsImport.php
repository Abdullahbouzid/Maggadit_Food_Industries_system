<?php
// app/Imports/PackOperationsImport.php

namespace App\Imports;

use App\Models\PackOperation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;

class PackOperationsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    protected $errors = [];

    public function model(array $row)
    {
        if (!isset($row['code']) || !isset($row['name_en']) || !isset($row['name_ar'])) {
            return null;
        }

        return new PackOperation([
            'code'       => $row['code'],
            'name_en'    => $row['name_en'],
            'name_ar'    => $row['name_ar'],
            'is_active'  => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'sort_order' => $row['sort_order'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code'    => 'required|string|max:255|unique:pack_operations,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}