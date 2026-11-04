<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VendorsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Vendor([
            'vendor_name' => $row['vendor_name'] ?? null,
            'code' => $row['code'] ?? null,
            'phone' => $row['phone'] ?? null,
            'fax' => $row['fax'] ?? null,
            'contact_person' => $row['contact_person'] ?? null,
            'contact_number' => $row['contact_number'] ?? null,
            'street' => $row['street'] ?? null,
            'commission_percentage' => isset($row['commission_percentage']) ? (float) $row['commission_percentage'] : null,
            'inactive' => isset($row['inactive']) ? filter_var($row['inactive'], FILTER_VALIDATE_BOOLEAN) : false,
        ]);
    }
}
