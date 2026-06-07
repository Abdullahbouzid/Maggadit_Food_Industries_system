<?php

namespace App\Imports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class DevicesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $errors = [];

    public function model(array $row)
    {
        if (Device::where('serial', $row['serial'])->exists()) {
            $this->errors[] = "Serial '{$row['serial']}' already exists. Skipped.";
            return null;
        }

        $organizationId = $row['organization_id'] ?? null;
        if ($organizationId && !\App\Models\Organization::find($organizationId)) {
            $this->errors[] = "Organization ID {$organizationId} not found for serial {$row['serial']}.";
            return null;
        }

        $brandId = $row['brand_id'] ?? null;
        if ($brandId && !\App\Models\Brand::find($brandId)) {
            $this->errors[] = "Brand ID {$brandId} not found for serial {$row['serial']}.";
            return null;
        }

        return new Device([
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'serial' => $row['serial'],
            'organization_id' => $organizationId,
            'model_no' => $row['model_no'] ?? null,
            'brand_id' => $brandId,
            'remark' => $row['remark'] ?? null,
            'purchase_date' => isset($row['purchase_date']) ? Carbon::parse($row['purchase_date']) : null,
            'warranty_expiry' => isset($row['warranty_expiry']) ? Carbon::parse($row['warranty_expiry']) : null,
            'is_inactive' => filter_var($row['is_inactive'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $row['sort_order'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'serial' => 'required|string|max:100|unique:devices,serial',
            'organization_id' => 'required|exists:organizations,id',
            'brand_id' => 'nullable|exists:brands,id',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}