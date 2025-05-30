<?php

namespace App\Entities\Setting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = "api_keys";
    protected $fillable = ['name', 'key', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($key) {
            $key->key = Str::uuid()->toString();
        });
    }
}
