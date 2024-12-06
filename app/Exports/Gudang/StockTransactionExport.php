<?php

namespace App\Exports\Gudang;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StockTransactionExport implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('superuser.gudang.stock.stock_transactions', [
            'transactions' => $this->data['collects'] // Ensure the key matches
        ]);
    }
}
