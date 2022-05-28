<?php

namespace App\Entities\Setting;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = "setting_menu";
    protected $fillable = [
    	'name',
    	'route_name',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function user_menu(){
    	return $this->hasMany('App\Entities\Setting\Menu','menu_id');
    }
}
