<?php

namespace App\Entities\Utility;

use DB;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    public function user() {
        $user = DB::table('model_has_roles')->where('role_id', $this->id);

        return $user;
    }
}
