<?php

namespace App\DataTables;

use App\DataTables\Table;
use App\Entities\Boilerplate;
use App\Entities\BoilerplateImage;
use Carbon\Carbon;

class BoilerplateTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        switch ($this->show) {
          case 'default':
            $model = Boilerplate::select('id', 'text', 'textarea', 'select', 'select_multiple', 'image', 'created_at', 'deleted_at');
            break;
          case 'trash':
            $model = Boilerplate::onlyTrashed()->select('id', 'text', 'textarea', 'select', 'select_multiple', 'image', 'created_at', 'deleted_at');
            break;
          case 'all':
            $model = Boilerplate::withTrashed()->select('id', 'text', 'textarea', 'select', 'select_multiple', 'image', 'created_at', 'deleted_at');
            break;
          default:
            $model = Boilerplate::select('id', 'text', 'textarea', 'select', 'select_multiple', 'image', 'created_at', 'deleted_at');
            break;
        }

        return $model;
    }
    
    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();
        $table->setRowClass(function (Boilerplate $model) {
            return $model->trashed() ? 'table-danger' : '';
        });
        $table->editColumn('image', function (Boilerplate $model) {
            return "
              <a class=\"img-link img-link-zoom-in img-lightbox\" href=\"{$model->image_url}\">
                <img class=\"img-fluid img-table\" src=\"{$model->image_url}\">
              </a>
            ";
        });
        $table->editColumn('created_at', function (Boilerplate $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y'),
              'timestamp' => $model->created_at
            ];
        });
        $table->addColumn('action', function (Boilerplate $model) {
            $view = route('superuser.boilerplate.show', $model);
            $edit = route('superuser.boilerplate.edit', $model);
            $restore = route('superuser.boilerplate.restore', $model);
            $destroy = route('superuser.boilerplate.destroy', $model);
            $destroy_permanent = route('superuser.boilerplate.destroy_permanent', $model);

            if ($model->trashed()) {
                return "
                  <a href=\"javascript:restoreConfirmation('{$restore}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Restore\">
                      <i class=\"fa fa-undo\"></i>
                    </button>
                  </a>
                  <a href=\"javascript:deleteConfirmation('{$destroy_permanent}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete Permanent\">
                      <i class=\"fa fa-trash\"></i>
                    </button>
                  </a>
                ";
            } else {
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
            }
        });

        $table->rawColumns(['image', 'action']);

        return $table->make(true);
    }
}
