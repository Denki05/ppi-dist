<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerOtherAddressTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = CustomerOtherAddress::select(
            'master_customer_other_addresses.id AS id', 
            'master_customer_other_addresses.name AS member_name', 
            'master_customer_other_addresses.text_kota AS member_kota', 
            'master_customer_other_addresses.status AS status', 
            'master_customers.id AS store_id', 
            'master_customer_categories.id AS id_category', 
            'master_customer_categories.name AS category_name', 
        );

        $model = $model->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id');

        $model = $model->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id');

        if($request->member_name != 'all') {
            $model = $model->where('master_customer_other_addresses.id', $request->member_name);
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

        $table->setRowClass(function (CustomerOtherAddress $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (CustomerOtherAddress $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (CustomerOtherAddress $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (CustomerOtherAddress $model) {
            $add_so = route('superuser.penjualan.sales_order.create', ['store' => $model->store_id, 'step' => 1, 'member' => $model->id]);
            
            return "
                <a href=\"{$add_so}\" class=\"btn btn-primary btn-lg active\" role=\"button\" aria-pressed=\"true\">Add Sales Order</a>
            ";
        });
        
        return $table->make(true);
    }
}