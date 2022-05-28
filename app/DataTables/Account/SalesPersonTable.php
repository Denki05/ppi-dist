<?php

namespace App\DataTables\Account;

use App\DataTables\Table;
use App\Entities\Account\SalesPerson;
use Carbon\Carbon;

class SalesPersonTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = SalesPerson::select('id', 'username', 'email', 'name', 'is_active');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (SalesPerson $model) {
            return !$model->is_active ? 'table-danger' : '';
        });

        $table->editColumn('is_active', function (SalesPerson $model) {
            $active = '<i class="fa fa-lg fa-check text-success"></i>';
            $inactive = '<i class="fa fa-lg fa-close text-danger"></i>';

            return ($model->is_active) ? $active : $inactive;
        });

        $table->addColumn('action', function (SalesPerson $model) {
            $view = route('superuser.account.sales_person.show', $model);
            $edit = route('superuser.account.sales_person.edit', $model);
            $destroy = route('superuser.account.sales_person.destroy', $model);
            $restore = route('superuser.account.sales_person.restore', $model);

            if ($model->is_active) {
                $toggle_status = "
                    <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                            <i class=\"fa fa-times\"></i>
                        </button>
                    </a>
                ";

                $toggle_edit = "
                    <a href=\"{$edit}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                            <i class=\"fa fa-pencil\"></i>
                        </button>
                    </a>
                ";
            } else {
                $toggle_status = "
                    <a href=\"javascript:restoreConfirmation('{$restore}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Restore\">
                            <i class=\"fa fa-undo\"></i>
                        </button>
                    </a>
                ";

                $toggle_edit = "";
            }

            return "
                <a href=\"{$view}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                        <i class=\"fa fa-eye\"></i>
                    </button>
                </a>
                $toggle_edit
                $toggle_status
            ";
        });

        $table->rawColumns(['is_active', 'image', 'action']);

        return $table->make(true);
    }
}