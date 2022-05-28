<?php

namespace App\Imports\Master;

use App\Entities\Master\Contact;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ContactImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['name', 'phone', 'email', 'position', 'dob', 'npwp', 'ktp', 'address'], $row);

        return new Contact([
            'name' => $row['name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'position' => $row['position'],
            'dob' => ($row['dob'] != null) ? date('Y-m-d', strtotime($row['dob'])) : null,
            'npwp' => $row['npwp'],
            'ktp' => $row['ktp'],
            'address' => $row['address'],
            'status' => Contact::STATUS['ACTIVE'],
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'name' => 'required',
            'phone' => 'required',
            'dob' => 'nullable|date|date_format:d-m-Y'
        ];
    }
}
