<?php

namespace App\Http\Controllers\Superuser\Account;

use App\Entities\Account\SalesPerson;
use App\Entities\Account\SalesPersonBranchOffice;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Validator;

class SalesPersonBranchOfficeController extends Controller
{
    public function manage($id)
    {
        $data['sales_person'] = SalesPerson::findOrFail($id);
        $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.account.sales_person.branch_office', $data);
    }

    public function add(Request $request, $id)
    {
        $sales_person = SalesPerson::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'branch_office' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = SalesPersonBranchOffice::where([
            'sales_person_id' => $sales_person->id,
            'branch_office_id' => $request->branch_office,
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Branch Office already exists']);
        }

        $branch_office = new SalesPersonBranchOffice;

        $branch_office->sales_person_id = $sales_person->id;
        $branch_office->branch_office_id = $request->branch_office;

        $branch_office->save();
        
        return redirect()->back();
    }

    public function remove($id, $branch_office_id)
    {
        $sales_person = SalesPerson::findOrFail($id);
        $branch_office = SalesPersonBranchOffice::findOrFail($branch_office_id);

        $branch_office->delete();

        return redirect()->back();
    }
}
