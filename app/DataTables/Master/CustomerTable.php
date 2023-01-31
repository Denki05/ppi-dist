<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {
        $model = Customer::select(
                           'master_customers.name as name', 
                           'master_customers.id as id', 
                           'master_customers.status as status',
                           'master_customer_other_addresses.id as member_id'
                        );
                    
                    $model = $model->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id');

                    if($request->customer_name != 'all') {
                        $model = $model->where('master_customers.id', $request->customer_name);
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

        $table->setRowClass(function (Customer $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (Customer $model) {
            return $model->status();
        });

        

        $table->addColumn('action', function (Customer $model) {
            $view = route('superuser.master.customer.show', $model);
            $edit = route('superuser.penjualan.sales_order.create', $model);
            $destroy = route('superuser.master.customer.destroy', $model);

            if ($model->status == $model::STATUS['DELETED']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }
            
            return "
                
                <a href=\"{$edit}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Add SO\">
                        <i class=\"fa fa-plus\"></i>
                    </button>
                </a>
                
            ";
        });
        
        return $table->make(true);
    }
}