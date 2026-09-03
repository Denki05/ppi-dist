<?php

namespace App\Exports\Penjualan;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class QaChecklistExport implements FromView
{
    public function view(): View
    {
        return view('superuser.penjualan.qa_checklist_excel');
    }
}
