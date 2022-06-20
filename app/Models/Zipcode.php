<?php

/*
 * This file is part of the IndoRegion package.
 *
 * (c) Azis Hapidin <azishapidin.com | azishapidin@gmail.com>
 *
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Village;

/**
 * Village Model.
 */
class Zipcode extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'tbl_zipcode';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'postal_id'
    ];

	/**
     * Village belongs to District.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
