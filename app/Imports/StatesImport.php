<?php

namespace App\Imports;

use App\Models\State;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class StatesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    // private $errors = [ 'Import Errors:  ' ,'-------------------'];

    public function model(array $row)
    {
        if (State::where('code', $row['code'])->exists()) {
            $this->errors[] = "Code '{$row['code']}' already exists. Skipped.";
            return null;
        }

        return new State([
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
            'code' => 'required|string|max:50|unique:states,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}