<?php

namespace App\Http\Controllers\Superuser\Account;

use App\Entities\Account\SalesPerson;
use App\Entities\Account\SalesPersonWarehouse;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Validator;

class SalesPersonWarehouseController extends Controller
{
    public function manage($id)
    {
        $data['sales_person'] = SalesPerson::findOrFail($id);
        $data['warehouses'] = MasterRepo::warehouses();

        return view('superuser.account.sales_person.warehouse', $data);
    }

    public function add(Request $request, $id)
    {
        $sales_person = SalesPerson::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'warehouse' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = SalesPersonWarehouse::where([
            'sales_person_id' => $sales_person->id,
            'warehouse_id' => $request->warehouse,
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Warehouse already exists']);
        }

        $warehouse = new SalesPersonWarehouse;

        $warehouse->sales_person_id = $sales_person->id;
        $warehouse->warehouse_id = $request->warehouse;

        $warehouse->save();
        
        return redirect()->back();
    }

    public function remove($id, $warehouse_id)
    {
        $sales_person = SalesPerson::findOrFail($id);
        $warehouse = SalesPersonWarehouse::findOrFail($warehouse_id);

        $warehouse->delete();

        return redirect()->back();
    }
}
