<?php

namespace App\Entities\Setting;

use Illuminate\Database\Eloquent\Model;

class UserMenu extends Model
{
    protected $table = "setting_user_menu";
    protected $fillable = [
    	'user_id',
    	'menu_id',
        'can_read',
        'can_create',
        'can_update',
        'can_delete',
        'can_approve',
        'can_print',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function user(){
    	return $this->BelongsTo('App\Entities\Account\User','user_id','id');
    }
    public function menu(){
    	return $this->BelongsTo('App\Entities\Setting\Menu','menu_id','id');
    }
}
