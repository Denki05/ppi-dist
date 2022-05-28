<?php

namespace App\Entities\Account;

use App\Observers\ResponsibleUserObserver;
use DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class Superuser extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table               = 'superusers';
    protected $guard_name          = 'superuser';
    protected $appends             = ['img'];
    public static $directory_image = 'superuser_assets/media/profiles/';

    const IS_SUPERUSER = [
        1 => 'Superuser',
        0 => 'User'
    ];

    public static function boot() {
        parent::boot();

        static::observe(new ResponsibleUserObserver());
    }

    public function canAny(array $permissions)
    {
        foreach($permissions as $permission) {
            if ($this->can($permission) ) {
                return true;
            }
        }
        
        return false;
    }

    public function getImgAttribute()
    {
        if (!$this->image OR !file_exists(self::$directory_image.$this->image)) {
            return img_holder('avatar');
        }

        return asset(self::$directory_image.$this->image);
    }

    public function dontHaveRoles()
    {
        $roles = DB::table('roles')->whereNotIn('id', $this->roles()->pluck('id'));

        if (!Auth::guard('superuser')->user()->hasRole('Developer')) {
            $roles->where('name', '!=', 'Developer')
                  ->where('name', '!=', 'SuperAdmin');
        }

        return $roles->get();
    }

    public function createdBySuperuser()
    {
        $superuser = static::find($this->created_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function updatedBySuperuser()
    {
        $superuser = static::find($this->updated_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function deletedBySuperuser()
    {
        $superuser = static::find($this->deleted_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}

    public function is_superuser(){
        return (object) self::IS_SUPERUSER[$this->is_superuser];
    }
}
