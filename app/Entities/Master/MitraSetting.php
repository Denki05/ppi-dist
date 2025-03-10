<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MitraSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mitra_id', 
        'bulan',
        'batas_bawah', 
        'batas_atas',
        'saldo', 
        'created_by', 
        'updated_by', 
    ];

    protected $table = 'master_setting_mitra';
    
    public function mitra()
    {
        return $this->belongsTo('App\Entities\Master\Mitra', 'mitra_id', 'id');
    }
}