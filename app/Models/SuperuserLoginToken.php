<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperuserLoginToken extends Model
{
    protected $table = 'superuser_login_tokens';

    protected $fillable = [
        'superuser_id',
        'token',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public $timestamps = false; // Tambahkan jika tabel tidak punya timestamps

    public function superuser()
    {
        return $this->belongsTo(\App\Entities\Account\Superuser::class, 'superuser_id');
    }
}
