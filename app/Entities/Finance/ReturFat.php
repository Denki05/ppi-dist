<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;

class ReturFat extends Model
{   
    protected $table = "finance_retur";
    protected $fillable = [
        'code',
    	'do_id',
    	'retur_id',
        'do_new_id', 
    	'total_nota',
        'total_retur',
        'total_nota_new',
        'total_final',
        'type_retur',
        'payment_retur', 
        'status',
    	'created_by',
    	'updated_by',
    ];

    const TYPE_RETUR = [
        'RETUR' => 1,
        'TUKAR BARANG' => 2,
    ];

    const PAYMENT_RETUR = [
        'BELUM LUNAS' => 0,
        'LUNAS' => 1,
    ];

    const STATUS = [
        'VERIFIKASI' => 0,
        'ACC' => 1,
        'DONE' => 2,
    ];

    public function type_retur()
    {
        return array_search($this->type_retur, self::TYPE_RETUR);
    }

    public function payment_retur()
    {
        return array_search($this->payment_retur, self::PAYMENT_RETUR);
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function saleReturn()
    {
        return $this->belongsTo('App\Entities\Penjualan\SaleReturn', 'retur_id', 'id');
    }
}
