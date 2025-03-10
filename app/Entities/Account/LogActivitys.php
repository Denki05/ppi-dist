<?php

namespace App\Entities\Account;

use Illuminate\Database\Eloquent\Model;

class LogActivitys extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "log_activities";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'url', 'method', 'ip', 'agent', 'user_id'
    ];

    public function created_by(){
        return $this->belongsTo('App\Entities\Account\Superuser', 'user_id', 'id');
    }
}