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
            $view = route('superuser.master.customer_other_address.show', $model);
            $edit = route('superuser.master.customer_other_address.edit', $model);
            $destroy = route('superuser.master.customer_other_address.destroy', $model);

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
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });
        
        return $table->make(true);
    }
}