<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Accounting\InvoiceTax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class InvoiceTaxBeliTable extends Table
{
    private function query(Request $request)
    {
        $month = $request->input('bulan', now()->month);
        $year = $request->input('tahun', now()->year);

        $model = InvoiceTax::leftJoin('master_mitra', 'finance_invoice_mitra.mitra_id', '=', 'master_mitra.id')
            ->select(
                'finance_invoice_mitra.date as tanggal', 
                'finance_invoice_mitra.code as code', 
                'master_mitra.name as mitra', 
                DB::raw('
                    CASE 
                        WHEN finance_invoice_mitra.type = 1 THEN "JUAL"
                        WHEN finance_invoice_mitra.type = 2 THEN "BELI"
                        ELSE "NONE"
                    END AS type
                ')
            )
            ->where('finance_invoice_mitra.type', 2)
            ->where('finance_invoice_mitra.status',1)
            ->whereMonth('finance_invoice_mitra.date', $month)
            ->whereYear('finance_invoice_mitra.date', $year) // Add year filter
            ->get();

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('action', function (InvoiceTax $model) {
            if ($model->status == $model::STATUS['ACTIVE']) {
                
                return "
                    <button 
                        class=\"btn btn-sm btn-primary\" 
                        data-toggle=\"modal\" 
                        data-target=\"#myModal' . $model->id . '\">
                        View
                    </button>
                ";
            }
        });

        return $table->make(true);
    }
}