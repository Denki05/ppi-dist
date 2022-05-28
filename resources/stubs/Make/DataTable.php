<?php

namespace DummyNamespace;

use App\DataTables\Table;
use Carbon\Carbon;

class DummyClass extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {

    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        return $table->make(true);
    }
}