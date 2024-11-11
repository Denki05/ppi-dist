<?php

namespace App\Entities\Account;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
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

    /**
     * Get the user who created this log activity.
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Entities\Account\Superuser', 'user_id', 'id');
    }
}
