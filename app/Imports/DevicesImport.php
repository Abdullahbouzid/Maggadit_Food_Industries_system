<?php

namespace App\Imports;

use App\Models\Device;
use App\Models\Organization;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DevicesImport implements SkipsOnError, ToModel, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    private $errors = [];

    public function model(array $row)
    {
        if (Device::where('serial', $row['serial'])->exists()) {
            $this->errors[] = "Serial '{$row['serial']}' already exists. Skipped.";

            return null;
        }

        // التحقق من وجود المنظمة
        if (! Organization::find($row['organization_id'])) {
            $this->errors[] = "Organization ID {$row['organization_id']} not found for serial {$row['serial']}";

            return null;
        }

        return new Device([
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'serial' => $row['serial'],
            'organization_id' => $row['organization_id'],
            'model_no' => $row['model_no'] ?? null,
            'brand' => $row['brand'] ?? null,
            'remark' => $row['remark'] ?? null,
            'purchase_date' => isset($row['purchase_date']) ? Carbon::parse($row['purchase_date']) : null,
            'warranty_expiry' => isset($row['warranty_expiry']) ? Carbon::parse($row['warranty_expiry']) : null,
            'is_inactive' => filter_var($row['is_inactive'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'serial' => 'required|string|max:100|unique:devices,serial',
            'organization_id' => 'required|exists:organizations,id',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
