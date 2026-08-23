<?php

// app/Imports/SalesOrderTypesImport.php

namespace App\Imports;

use App\Models\SalesOrderType;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class SalesOrderTypesImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    protected $errors = [];

    public function model(array $row)
    {
        return new SalesOrderType([
            'name' => $row['name'],
            'code' => $row['code'],
            'active' => filter_var($row['active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'default' => filter_var($row['default'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:sales_order_types,name',
            'code' => 'required|string|max:50|unique:sales_order_types,code',
            'active' => 'nullable|boolean',
            'default' => 'nullable|boolean',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: ".implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
