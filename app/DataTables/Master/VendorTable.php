<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Vendor;
use Carbon\Carbon;

class VendorTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Vendor::select('id', 'code', 'name', 'type', 'status', 'created_at');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Vendor $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (Vendor $model) {
            return $model->status();
        });

        $table->editColumn('type', function (Vendor $model) {
            return $model->type();
        });

        $table->editColumn('created_at', function (Vendor $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Vendor $model) {
            $view = route('superuser.master.vendor.show', $model);
            $edit = route('superuser.master.vendor.edit', $model);
            $destroy = route('superuser.master.vendor.destroy', $model);

            if ($model->status == $model::STATUS['ACTIVE']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"mdi mdi-eye\"></i>
                        </button>
                    </a>
                    <a href=\"{$edit}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"mdi mdi-lead-pencil\"></i>
                        </button>
                    </a>
                    <a href=\"{$destroy}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"mdi mdi-delete\"></i>
                        </button>
                    </a>
                ";
            }

            return "
                        <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"View\">
                            <i class=\"mdi mdi-eye\"></i>
                        </button>
                        </a>
            ";
        });

        return $table->make(true);
    }
}