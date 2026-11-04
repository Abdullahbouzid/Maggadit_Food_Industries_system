<?php

namespace App\Imports;

use App\Models\Organization;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VehiclesImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    private $errors = [];

    public function model(array $row)
    {
        // Find organization by name or code
        $organization = Organization::where('name', $row['organization'])
            ->orWhere('code', $row['organization'])
            ->first();

        $vehicleType = VehicleType::where('name_en', $row['vehicle_type'])
            ->orWhere('name_ar', $row['vehicle_type'])
            ->orWhere('code', $row['vehicle_type'])
            ->first();

        if (! $organization || ! $vehicleType) {
            $this->errors[] = "Row with vehicle_name '{$row['vehicle_name']}' failed: Organization or Vehicle Type not found.";

            return null;
        }

        return new Vehicle([
            'vehicle_name' => $row['vehicle_name'],
            'vehicle_code' => $row['vehicle_code'],
            'plate_no' => $row['plate_no'],
            'barcode' => $row['barcode'] ?? null,
            'organization_id' => $organization->id,
            'vehicle_type_id' => $vehicleType->id,
            'odometer' => $row['odometer'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'amount' => $row['amount'] ?? null,
            'volume' => $row['volume'] ?? null,
            'min_load_qty' => $row['min_load_qty'] ?? null,
            'inactive' => isset($row['inactive']) && strtolower($row['inactive']) === 'inactive',
        ]);
    }

    public function rules(): array
    {
        return [
            'vehicle_name' => 'required|string',
            'vehicle_code' => 'required|unique:vehicles,vehicle_code',
            'plate_no' => 'required|unique:vehicles,plate_no',
            'organization' => 'required|string',
            'vehicle_type' => 'required|string',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
