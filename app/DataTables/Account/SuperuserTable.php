<?php

namespace App\DataTables\Account;

use App\DataTables\Table;
use App\Entities\Account\Superuser;
use Auth;

class SuperuserTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        if (Auth::guard('superuser')->user()->hasRole('Developer')) {
            return Superuser::select('id', 'username', 'email', 'name', 'image', 'is_active','is_superuser');
        } else {
            return Superuser::whereNotIn('id', [1])->select('id', 'username', 'email', 'name', 'image', 'is_active','is_superuser');
        }
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Superuser $model) {
            return !$model->is_active ? 'table-danger' : '';
        });

        $table->editColumn('is_active', function (Superuser $model) {
            $active = '<i class="fa fa-lg fa-check text-success"></i>';
            $inactive = '<i class="fa fa-lg fa-close text-danger"></i>';

            return ($model->is_active) ? $active : $inactive;
        });

        $table->editColumn('image', function (Superuser $model) {
            return "
              <a class=\"img-link img-link-zoom-in img-lightbox\" href=\"{$model->img}\">
                <img class=\"img-fluid img-table\" src=\"{$model->img}\">
              </a>
            ";
        });

        $table->addColumn('action', function (Superuser $model) {
            $view = route('superuser.account.superuser.show', $model);
            $edit = route('superuser.account.superuser.edit', $model);
            $destroy = route('superuser.account.superuser.destroy', $model);
            $restore = route('superuser.account.superuser.restore', $model);

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

            if (Auth::guard('superuser')->user()->hasRole('SuperAdmin') == false) {
                $toggle_status = '';
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

        $table->editColumn('is_superuser', function (Superuser $model) {
            return $model->is_superuser()->scalar ?? '';
        });
        
        $table->rawColumns(['is_active', 'image', 'action','is_superuser']);

        return $table->make(true);
    }
}