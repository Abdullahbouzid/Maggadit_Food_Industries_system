<?php

namespace App\Imports;

use App\Models\Area;
use App\Models\State;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AreasImport implements SkipsOnError, ToModel, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    // private $errors = [];

    public function model(array $row)
    {
        if (Area::where('code', $row['code'])->exists()) {
            $this->errors[] = "Code '{$row['code']}' already exists. Skipped.";

            return null;
        }

        // تحقق من وجود state_id إذا كان موجوداً
        $stateId = $row['state_id'] ?? null;
        if ($stateId && ! State::where('id', $stateId)->exists()) {
            $this->errors[] = "State ID '{$stateId}' does not exist for code '{$row['code']}'. Skipped.";

            return null;
        }

        return new Area([
            'code' => $row['code'],
            'name_en' => $row['name_en'],
            'name_ar' => $row['name_ar'],
            'description' => $row['description'] ?? null,
            'state_id' => $stateId,
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $row['sort_order'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:areas,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'state_id' => 'nullable|exists:states,id',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
