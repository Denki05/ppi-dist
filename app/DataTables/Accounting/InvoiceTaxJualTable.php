<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Accounting\InvoiceTax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class InvoiceTaxJualTable extends Table
{
    private function query(Request $request)
    {
        $month = $request->input('bulan', now()->month);
        $year = $request->input('tahun', now()->year);
        // $year = '2024';

        $model = InvoiceTax::leftJoin('master_mitra', 'finance_invoice_mitra.mitra_id', '=', 'master_mitra.id')
            ->select(
                'finance_invoice_mitra.id as id', 
                'finance_invoice_mitra.date as tanggal', 
                'finance_invoice_mitra.code as code', 
                'master_mitra.name as mitra', 
                DB::raw('
                    CASE 
                        WHEN finance_invoice_mitra.type = 1 THEN "JUAL"
                        WHEN finance_invoice_mitra.type = 2 THEN "BELI"
                        ELSE "NONE"
                    END AS type
                '),
                'finance_invoice_mitra.status as status',
            )
            ->where('finance_invoice_mitra.type', 1)
            // ->where('finance_invoice_mitra.status',1)
            ->whereMonth('finance_invoice_mitra.date', $month)
            ->whereYear('finance_invoice_mitra.date', $year) 
            ->get();

        // dd($model);

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('status', function (InvoiceTax $model) {
            return $model->status();
        });

        $table->addColumn('action', function (InvoiceTax $model) {
            if ($model->status == $model::STATUS['ACTIVE']) {
                $print = route('superuser.accounting.invoice_tax.print_invoice', $model->id);
                $destroy = route('superuser.accounting.invoice_tax.destroy_jual', $model->id);
                
                return "
                    <a href=\"{$print}\">
                        <button type=\"button\" class=\"btn btn-success btn-sm btn-flat\" title=\"Invoice Beli\">
                            <i class=\"fa fa-print\"></i> Print
                        </button>
                    </a>

                    <button 
                        type=\"button\" 
                        class=\"btn btn-primary btn-sm btn-flat\" 
                        data-toggle=\"modal\" 
                        data-target=\"#myModal{$model->id}\">
                        <i class=\"fa fa-eye\"></i> View
                    </button>

                    <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                        <button type=\"button\" class=\"btn btn-danger btn-sm btn-flat\" title=\"Destroy\">
                            <i class=\"fa fa-trash\"></i> Delete
                        </button>
                    </a>
                ";
            }
        });
        
        // Mark the column as raw to allow HTML rendering
        $table->rawColumns(['action']);

        return $table->make(true);
    }
}