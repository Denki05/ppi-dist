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
    public function query(Request $request)
    {
        $model = Customer::leftJoin('master_customer_categories', 'master_customer_categories.id', '=', 'master_customers.category_id')
            ->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->selectRaw('
                master_customers.id AS id,
                CONCAT(master_customers.name, " - ", master_customers.text_kota) AS store_name_city,
                master_customer_categories.name AS category_name, 
                master_customers.text_provinsi AS store_provinsi,
                master_customers.existence AS store_existence,
                CASE
                    WHEN master_customers.has_tempo = 0 THEN "NO"
                    WHEN master_customers.has_tempo = 1 THEN "YES"
                END AS store_tempo,
                master_customers.tempo_limit AS store_tempo_limit,
                master_customers.status AS status,
                master_customer_other_addresses.name AS member_name
            ')
            ->groupBy('master_customers.id');

            if ($request->type_search != 'all') {
                $model = $model->when($request->type_search == 0, function ($query) use ($request) {
                            return $query->where('master_customers.name', $request->search_name);
                        })
                        ->when($request->type_search == 1, function ($query) use ($request) {
                            return $query->where('master_customer_other_addresses.name', $request->search_name);
                        });
            }

        return $model->get();
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->setRowClass(function (Customer $model) {
            if ($model->status == $model::STATUS['DELETED']) {
                return 'table-danger';
            } else if ($model->status == $model::STATUS['INACTIVE']) {
                return 'table-warning';
            }
        });

        $table->editColumn('store_name_city', function (Customer $model) {
            return $model->store_name_city;
        });

        $table->addColumn('action', function (Customer $model) {
            $view = route('superuser.master.customer.show', $model->id);
            $edit = route('superuser.master.customer.edit', $model->id);
            $destroy = route('superuser.master.customer.destroy', $model->id);
            $add_member = route('superuser.master.customer.other_address.create', $model->id);

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

        $table->addColumn('action2', function (Customer $model) {
            $changeStatusActive = route('superuser.master.customer.changeStatusActive', $model->id);
            $changeStatusInactive = route('superuser.master.customer.changeStatusInactive', $model->id);
            $changeExistenceEnable = route('superuser.master.customer.changeExistenceEnable', $model->id);
            $changeExistenceDisabled = route('superuser.master.customer.changeExistenceDisabled', $model->id);
        
            $actions = '';
        
            if ($model->status == $model::STATUS['ACTIVE']) {
                $actions .= "
                    <a href=\"javascript:saveConfirmation('{$changeStatusInactive}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Change Inactive\">
                            <i class=\"fa fa-unlock-alt\"></i>
                        </button>
                    </a>
                ";
            }
        
            if ($model->status == $model::STATUS['INACTIVE']) {
                $actions .= "
                    <a href=\"javascript:saveConfirmation('{$changeStatusActive}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Change Active\">
                            <i class=\"fa fa-lock\"></i>
                        </button>
                    </a>
                ";
            }
        
            if ($model->store_existence == $model::EXISTENCE['DISABLED']) {
                $actions .= "
                <a href=\"javascript:saveConfirmation('{$changeExistenceEnable}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Change Enabled\">
                            <i class=\"fa fa-toggle-on\"></i>
                        </button>
                    </a>
                ";
            }
        
            if ($model->store_existence == $model::EXISTENCE['ENABLE']) {
                $actions .= "
                <a href=\"javascript:saveConfirmation('{$changeExistenceDisabled}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Change Disabled\">
                            <i class=\"fa fa-toggle-off\"></i>
                        </button>
                    </a>
                ";
            }
        
            return $actions;
        });

        $table->editColumn('detail', function (Customer $model) {
            // Initialize the detail HTML table structure
            $detail_html = '
            <table class="table" style="margin-top: -5px !important; margin-bottom: 0px;">
                <thead class="thead-dark">
                    <tr>
                        <th class="w-20">Member</th>
                        <th class="w-20">Kota</th>
                        <th class="w-20">Maps</th>
                        <th class="w-20">Default</th>
                        <th class="w-20">Action</th>
                    </tr>
                </thead>
                <tbody>';
        
            // Check if there are members associated with the customer
            $members = $model->member_count();
            if (count($members)) {
                foreach ($members as $history) {
                    $detail_html .= '
                    <tr>
                        <td>' . $history['member_name'] . '</td>
                        <td>' . $history['member_city'] . '</td>
                        <td>
                            <iframe style="height: 100px; width: 200px;" 
                                    src="https://maps.google.com/maps?q=' . $history['member_latitude'] . ',' . $history['member_longtitude'] . '&hl=es;z=14&output=embed">
                            </iframe>
                        </td>
                        <td>' . $history['member_default'] . '</td>
                        <td>
                            <a href="' . route('superuser.master.customer_other_address.show', $history['member_id']) . '">
                                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </a>
                            <a href="' . route('superuser.master.customer_other_address.edit', $history['member_id']) . '">
                                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </a>
                            <a href="' . route('superuser.master.customer_other_address.destroy', $history['member_id']) . '">
                                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </a>';
                }
            } else {
                // If no members are found
                $detail_html .= '
                <tr>
                    <td colspan="5">Nothing Found</td>
                </tr>';
            }
        
            // Close the table body and return the HTML
            $detail_html .= '
                </tbody>
            </table>';
        
            return $detail_html;
        });

        $table->rawColumns(['detail', 'action', 'action2', 'status_and_existence']);
        return $table->make(true);
    }
}