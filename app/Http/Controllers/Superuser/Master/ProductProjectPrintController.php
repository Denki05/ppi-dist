<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\ProductProjectPrint;
use App\Exports\Master\ProductProjectImportTemplate;
use App\Imports\Master\ProductProjectImport;
use Excel;
use Validator;
use Auth;
use DB;

class ProductProjectPrintController extends Controller
{
    public function import_template()
    {
        $filename = 'product-project-print-import-template.xlsx';
        return Excel::download(new ProductProjectImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            Excel::import(new ProductProjectImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function print(Request $request)
    {
        // Access control
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Ambil data dari database
        $products = ProductProjectPrint::where('tipe', $request->brand_name)->get();

        // Kirim ke view
        return view('superuser.master.product_project_print.print', compact('products'));
    }

}
