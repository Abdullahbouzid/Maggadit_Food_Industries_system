<?php
// app/Imports/ItemsImport.php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;

class ItemsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;
    protected $errors = [];
    public function model(array $row)
    {
        return new Item([
            'code' => $row['code'],
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'definition' => $row['definition'] ?? null,
            'item_type_id' => $row['item_type_id'] ?? null,
            'category_id' => $row['category_id'] ?? null,
            'brand_id' => $row['brand_id'] ?? null,
            'model_id' => $row['model_id'] ?? null,
            'supplier_id' => $row['supplier_id'] ?? null,
            'shelf_life_time' => $row['shelf_life_time'] ?? null,
            'sequence' => $row['sequence'] ?? null,
            'van_stock_tolerance' => $row['van_stock_tolerance'] ?? null,
            'storage_tolerance' => $row['storage_tolerance'] ?? null,
            'weight_tolerance' => $row['weight_tolerance'] ?? null,
            'low_stock_qty' => $row['low_stock_qty'] ?? null,
            'is_inactive' => filter_var($row['inactive'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_new' => filter_var($row['is_new'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_price_protected' => filter_var($row['is_price_protected'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_power_sku' => filter_var($row['is_power_sku'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_default_batch' => filter_var($row['is_default_batch'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_temp_credit' => filter_var($row['is_temp_credit'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_batch_priced' => filter_var($row['is_batch_priced'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_initiative' => filter_var($row['is_initiative'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'force_default_pack' => filter_var($row['force_default_pack'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'has_serial_number' => filter_var($row['has_serial_number'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'required_by_liter' => filter_var($row['required_by_liter'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'required_by_weight' => filter_var($row['required_by_weight'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
    public function rules(): array { return ['code' => 'required|string|max:255|unique:items,code', 'name_en' => 'required|string|max:255', 'name_ar' => 'required|string|max:255']; }
    public function onFailure(Failure ...$failures) { foreach ($failures as $failure) { $this->errors[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors()); } }
    public function getErrors() { return $this->errors; }
}