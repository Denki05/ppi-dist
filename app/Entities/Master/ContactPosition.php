<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactPosition extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'master_contact_position';
}
