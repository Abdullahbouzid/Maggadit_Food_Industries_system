<?php

// app/Imports/ItemTypesImport.php

namespace App\Imports;

use App\Models\ItemType;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class ItemTypesImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    protected $errors = [];

    public function model(array $row)
    {
        // التأكد من وجود البيانات الأساسية
        if (! isset($row['code']) || ! isset($row['name'])) {
            return null;
        }

        return new ItemType([
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'sort_order' => $row['sort_order'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:item_types,code',
            'name' => 'required|string|max:255',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = 'Row '.$failure->row().': '.implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
