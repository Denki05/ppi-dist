<?php

namespace App\Entities;

use App\Observers\ResponsibleUserObserver;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        $class = get_called_class();

        $class::observe(new ResponsibleUserObserver);
    }

    // public function disableDynamicAccessors()
    // {
    //     $this->setAppends([]);
    // }

    public function status()
    {
        if (isset($this->status)) {
            $class = get_called_class();
            
            return array_search($this->status, $class::STATUS);
        }
    }

    public function createdBySuperuser()
    {
        $superuser = Superuser::find($this->created_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function updatedBySuperuser()
    {
        $superuser = Superuser::find($this->updated_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function deletedBySuperuser()
    {
        $superuser = Superuser::find($this->deleted_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
}
