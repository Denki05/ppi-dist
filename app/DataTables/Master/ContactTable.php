<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Contact;
use Carbon\Carbon;

class ContactTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Contact::select('id', 'name', 'customer_id', 'phone', 'email', 'position', 'dob', 'npwp', 'ktp', 'address', 'status', 'created_at');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Contact $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });
        
        $table->editColumn('status', function (Contact $model) {
            return $model->status();
        });

        $table->editColumn('customer_id', function (Contact $model) {
            return $model->member->name;
        });

        $table->editColumn('created_at', function (Contact $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Contact $model) {
            $view = route('superuser.master.contact.show', $model);
            $edit = route('superuser.master.contact.edit', $model);
            $destroy = route('superuser.master.contact.destroy', $model);

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