<?php

namespace App\Imports\Master;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerType;
use App\Repositories\CodeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
class CustomerImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'name', /* 'code', */ 'category_id', /* 'type_id', */
            'email', 'phone', 'npwp', 'address',
            'owner_name', 'plafon_piutang', 'gps_latitude', 'gps_longitude',
            'zipcode'
        ], $row);

        return new Customer([
            'category_id' => $row['category_id'],
            // 'type_id' => $row['type_id'],
            'name' => $row['name'],
            'code' => CodeRepo::generateCustomer(),
            'email' => $row['email'],
            'phone' => $row['phone'],
            'npwp' => $row['npwp'],
            'address' => $row['address'],
            'owner_name' => $row['owner_name'],
            'plafon_piutang' => ($row['plafon_piutang'] != null) ? $row['plafon_piutang'] : 0,
            'zipcode' => $row['zipcode'],
            'status' => Customer::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'name' => 'required',
            // 'code' => 'required|' . Rule::unique('master_customers', 'code'),
            'category_id' => 'required|' . Rule::in(CustomerCategory::select('id')->pluck('id')->toArray()),
            // 'type_id' => 'required|' . Rule::in(CustomerType::select('id')->pluck('id')->toArray()),
            'address' => 'required',
        ];
    }
}
