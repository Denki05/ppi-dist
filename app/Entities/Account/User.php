<?php

namespace App\Entities\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
 

class User extends Model
{
    use Notifiable;

    protected $table = "superusers";
    protected $fillable = [
    	'name',
    	'email',
    	'username',
    	'password',
    	'division',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
    	'is_superuser',
        'pass_code', 
    	'is_active',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    const IS_SUPERUSER = [
        1 => 'Superuser',
        0 => 'User'
    ];

    const IS_ACTIVE = [
        1 => 'Active',
        0 => 'Non Active'
    ];


    public function user_menu(){
        return $this->hasMany('App\Entities\Setting\UserMenu','user_id');
    }
    public function is_superuser(){
        return (object) self::IS_SUPERUSER[$this->is_superuser];
    }
    public function is_active(){
        return (object) self::IS_ACTIVE[$this->is_active];
    }

    public function isPassCodeNull()
    {
        return $this->pass_code === null;
    }
}
