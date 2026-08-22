<?php

namespace App\Imports;

use App\Models\VehicleType;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VehicleTypesImport implements SkipsOnError, ToModel, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    private $errors = [];

    public function model(array $row)
    {
        if (VehicleType::where('code', $row['code'])->exists()) {
            $this->errors[] = "Code '{$row['code']}' already exists. Skipped.";

            return null;
        }

        return new VehicleType([
            'code' => $row['code'],
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'description' => $row['description'] ?? null,
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $row['sort_order'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:vehicle_types,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
