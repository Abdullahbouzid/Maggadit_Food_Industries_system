<?php

namespace App\Imports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class WarehousesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $errors = [];

    public function model(array $row)
    {
        if (Warehouse::where('code', $row['code'])->exists()) {
            $this->errors[] = "Code '{$row['code']}' already exists. Skipped.";
            return null;
        }

        // Validate foreign keys existence
        if (!\App\Models\WarehouseType::find($row['warehouse_type_id'])) {
            $this->errors[] = "Warehouse Type ID {$row['warehouse_type_id']} not found for code {$row['code']}";
            return null;
        }
        if (!\App\Models\Organization::find($row['organization_id'])) {
            $this->errors[] = "Organization ID {$row['organization_id']} not found for code {$row['code']}";
            return null;
        }

        return new Warehouse([
            'code' => $row['code'],
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'warehouse_type_id' => $row['warehouse_type_id'],
            'organization_id' => $row['organization_id'],
            'phone' => $row['phone'] ?? null,
            'fax' => $row['fax'] ?? null,
            'barcode' => $row['barcode'] ?? null,
            'address_en' => $row['address_en'] ?? null,
            'address_ar' => $row['address_ar'] ?? null,
            'gps_latitude' => $row['gps_latitude'] ?? null,
            'gps_longitude' => $row['gps_longitude'] ?? null,
            'zip_code' => $row['zip_code'] ?? null,
            'country_id' => $row['country_id'] ?? null,
            'state_id' => $row['state_id'] ?? null,
            'city_id' => $row['city_id'] ?? null,
            'area_id' => $row['area_id'] ?? null,
            'street' => $row['street'] ?? null,
            'destinations' => isset($row['destinations']) ? json_encode(explode(',', $row['destinations'])) : null,
            'sources' => isset($row['sources']) ? json_encode(explode(',', $row['sources'])) : null,
            'employees' => isset($row['employees']) ? json_encode(explode(',', $row['employees'])) : null,
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $row['sort_order'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:100|unique:warehouses,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}