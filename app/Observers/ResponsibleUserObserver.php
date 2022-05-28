<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ResponsibleUserObserver
{
    protected $user_id = NULL;
    
    public function __construct()
    {
        $user = Auth::guard('superuser')->user();
        // $userApi = Auth::guard('api')->user();

        if ($user) {
            $this->user_id = $user->id;
        }
        
        // } else if ($userApi) {
        //     $this->user_id = $userApi->id;
        // }
    }

    public function creating($model)
    {
        if (Schema::hasColumn($model->getTable(), 'created_by')) {
            $model->created_by = $this->user_id;
        }
    }

    public function updating($model)
    {
        if (Schema::hasColumn($model->getTable(), 'updated_by')) {
            if (!($model->isDirty('deleted_at') AND $model->deleted_at == NULL)) {
                $model->updated_by = $this->user_id;
            }
        }
    }

    public function deleting($model)
    {
        if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
            // get event
            $dispatcher = get_class($model)::getEventDispatcher();

            // disable event to continue without observer
            get_class($model)::unsetEventDispatcher();

            if ( Schema::hasColumn($model->getTable(), 'code') ) {
                $model->code .= uniqid('__del_');
            }

            $model->deleted_by = $this->user_id;
            $model->save();

            // enable the event again
            get_class($model)::setEventDispatcher($dispatcher);
        }
    }

    public function restoring($model)
    {
        if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
            // get event
            $dispatcher = get_class($model)::getEventDispatcher();

            // disable event to continue without observer
            get_class($model)::unsetEventDispatcher();

            $model->deleted_by = NULL;
            $model->save();

            // enable the event again
            get_class($model)::setEventDispatcher($dispatcher);
        }   
    }
}
