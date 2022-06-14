<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Store;
use Carbon\Carbon;

class StoreTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Store::select('id', 'code', 'name', 'created_at');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        // $table->setRowClass(function (BranchOffice $model) {
        //     return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        // });
        
        // $table->editColumn('status', function (BranchOffice $model) {
        //     return $model->status();
        // });

        $table->editColumn('created_at', function (Store $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Store $model) {
            $view = route('superuser.master.store.show', $model);
            $edit = route('superuser.master.store.edit', $model);
            $destroy = route('superuser.master.store.destroy', $model);

            // if ($model->status == $model::STATUS['DELETED']) {
            //     return "
            //         <a href=\"{$view}\">
            //             <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
            //                 <i class=\"fa fa-eye\"></i>
            //             </button>
            //         </a>
            //     ";
            // }
            
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