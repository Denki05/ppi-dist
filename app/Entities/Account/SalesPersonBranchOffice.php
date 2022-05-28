<?php

namespace App\Entities\Account;

use App\Entities\Model;

class SalesPersonBranchOffice extends Model
{
    protected $fillable = ['sales_person_id', 'branch_office_id'];
    protected $table = 'sales_person_branch_offices';
}
