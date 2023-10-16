<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Finance\Invoicing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoicingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Invoicing::select(
            'finance_invoicing.id as id',
            'finance_invoicing.code as invoice_code', 
            'finance_invoicing.customer_other_address_id as member_id', 
            'finance_invoicing.grand_total_idr as invoice_total',
            'master_customer_other_addresses.id as id_customer',
            'master_customer_other_addresses.name as customer_name'
        );

        $model = $model->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'finance_invoicing.customer_other_address_id');

        if($request->member != 'all') {
            $model = $model->where('master_customer_other_addresses.id', $request->member);
        }

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();


        $table->editColumn('created_at', function (Invoicing $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        return $table->make(true);
    }
}