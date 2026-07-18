<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;

class StockAuditLog extends Model
{
    protected $table = 'do_stock_deduction_logs';

    protected $fillable = [
        'do_id', 'warehouse_id', 'product_packaging_id',
        'qty', 'note', 'status',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function productPackaging()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    // 👇 TAMBAHKAN RELASI INI 👇
    public function penjualanDo()
    {
        return $this->belongsTo('App\Entities\Penjualan\PackingOrder', 'do_id', 'id');
    }
}