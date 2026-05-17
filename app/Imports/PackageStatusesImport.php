<?php

namespace App\Imports;

use App\Models\PackageStatus;
use App\Models\Organization;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class PackageStatusesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private $errors = [];

    public function model(array $row)
    {
        // Verify organization exists
        if (!Organization::where('id', $row['organization_id'])->exists()) {
            $this->errors[] = "Organization ID {$row['organization_id']} does not exist (status: {$row['status_name']})";
            return null;
        }

        return new PackageStatus([
            'status_name' => $row['status_name'],
            'organization_id' => $row['organization_id'],
            'resellable' => $row['resellable'] ?? false,
            'expired' => $row['expired'] ?? false,
            'default' => $row['default'] ?? false,
            'lpo_required' => $row['lpo_required'] ?? false,
            'is_related_to_cn_request' => $row['is_related_to_cn_request'] ?? false,
            'is_price_editable' => $row['is_price_editable'] ?? false,
        ]);
    }

    public function rules(): array
    {
        return [
            'status_name' => 'required|string|max:255',
            'organization_id' => 'required|integer|exists:organizations,id',
            'resellable' => 'nullable|boolean',
            'expired' => 'nullable|boolean',
            'default' => 'nullable|boolean',
            'lpo_required' => 'nullable|boolean',
            'is_related_to_cn_request' => 'nullable|boolean',
            'is_price_editable' => 'nullable|boolean',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}