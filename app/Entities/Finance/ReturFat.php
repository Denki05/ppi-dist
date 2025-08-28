<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;

class ReturFat extends Model
{   
    protected $table = "finance_retur";
    protected $fillable = [
    	'do_id',
    	'retur_id',
        'do_new_id', 
    	'total_nota',
        'total_retur',
        'total_nota_new',
        'total_final',
        'type_retur',
        'status_retur', 
    	'created_by',
    	'updated_by',
    ];

    const TYPE_RETUR = [
        'RETUR' => 1,
        'TUKAR BARANG' => 2,
    ];

    const STATUS_RETUR = [
        'BELUM LUNAS' => 0,
        'LUNAS' => 1,
    ];

    public function type_retur()
    {
        return array_search($this->type_retur, self::TYPE_RETUR);
    }

    public function status_retur()
    {
        return array_search($this->status_retur, self::STATUS_RETUR);
    }
}
