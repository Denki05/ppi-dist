<?php

namespace App\Http\Controllers\Superuser\Account;

use App\Entities\Account\SalesPerson;
use App\Entities\Account\SalesPersonZone;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class SalesPersonZoneController extends Controller
{
    public function manage($id)
    {
        $data['sales_person'] = SalesPerson::findOrFail($id);

        return view('superuser.account.sales_person.zone', $data);
    }

    public function add(Request $request, $id)
    {
        $sales_person = SalesPerson::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'provinsi' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = SalesPersonZone::where([
            'sales_person_id' => $sales_person->id,
            'provinsi' => $request->provinsi,
            'text_provinsi' => $request->text_provinsi
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Zone already exists']);
        }

        $zone = new SalesPersonZone;

        $zone->sales_person_id = $sales_person->id;
        $zone->provinsi = $request->provinsi;
        $zone->text_provinsi = $request->text_provinsi;

        $zone->save();
        
        return redirect()->back();
    }

    public function remove($id, $zone_id)
    {
        $sales_person = SalesPerson::findOrFail($id);
        $zone = SalesPersonZone::findOrFail($zone_id);

        $zone->delete();

        return redirect()->back();
    }
}
