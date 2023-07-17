<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class CustomerTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Customer::select(
            'master_customers.id as id',
            'master_customers.name as store_name', 
            'master_customers.text_kota as store_kota', 
            'master_customers.text_provinsi as store_provinsi', 
            'master_customers.has_tempo as store_tempo', 
            'master_customers.tempo_limit as store_limit', 
            'master_customers.status',
            'master_customer_other_addresses.id as member_id',
            'master_customer_other_addresses.name as member_name', 
            'master_customer_other_addresses.text_kota as member_kota', 
            'master_customer_other_addresses.text_provinsi as member_provinsi',
            'master_customer_other_addresses.member_default as member_default', 
            'master_customer_categories.id as cat_id',
            'master_customer_categories.name as category_name'
        );

        $model = $model->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id');

        $model = $model->leftJoin('master_customer_categories', 'master_customer_categories.id', '=', 'master_customers.category_id');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->setRowClass(function (Customer $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (Customer $model) {
            return $model->status();
        });

        $table->editColumn('action_member', function (Customer $model) {
            
        });

        $table->addColumn('action', function (Customer $model) {
            $view = route('superuser.master.customer.show', $model);
            $edit = route('superuser.master.customer.edit', $model);
            $destroy = route('superuser.master.customer.destroy', $model);
            $add_member = route('superuser.master.customer.other_address.create', $model);

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
                <a href=\"{$view}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                        <i class=\"fa fa-eye\"></i>
                    </button>
                </a>
                <a href=\"{$edit}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                        <i class=\"fa fa-pencil\"></i>
                    </button>
                </a>
                <a href=\"{$add_member}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Add member\">
                        <i class=\"fa fa-user\"></i>
                    </button>
                </a>
                
                <a href=\"{$destroy}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-trash\"></i>
                    </button>
                </a>
                
            ";
        });
        
        return $table->make(true);
    }
}