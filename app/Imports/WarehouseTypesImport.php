<?php

namespace App\Imports;

use App\Models\WarehouseType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class WarehouseTypesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    // private $errors = [ 'Import Errors: '];

    public function model(array $row)
    {
        // التحقق من وجود code مكرر في نفس الملف أو قاعدة البيانات
        if (WarehouseType::where('code', $row['code'])->exists()) {
            $this->errors[] = "Code '{$row['code']}' already exists. Skipped.";
            return null;
        }

        return new WarehouseType([
            'code'        => $row['code'],
            'name_en'     => $row['name_en'],
            'name_ar'     => $row['name_ar'],
            'description' => $row['description'] ?? null,
            'is_active'   => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order'  => $row['sort_order'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:warehouse_types,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}