<?php

namespace App\Imports;

use App\Models\Organization;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Validator;

class OrganizationsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private $errors = [];

    public function model(array $row)
    {
        if (!empty($row['parent_id']) && !Organization::where('id', $row['parent_id'])->exists()) {
            $this->errors[] = "Parent ID {$row['parent_id']} غير موجود (للصف الذي به كود {$row['code']})";
            return null;
        }

        return new Organization([
            'name'              => $row['name'],
            'code'              => $row['code'],
            'parent_id'         => $row['parent_id'] ?? null,
            'organization_type' => $row['organization_type'] ?? null,
            'gln'               => $row['gln'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:organizations,code',
            'gln'  => 'nullable|string|max:255|unique:organizations,gln',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "الصف {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}