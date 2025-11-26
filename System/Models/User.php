<?php

namespace App\Extensions\RBAC\System\Models;

class User extends \App\Models\User
{
    use \App\Extensions\RBAC\System\Traits\DataAccessModel;
    protected $table = 'users';

    public function managers() 
    {
        return $this->morphToMany(
            "\App\Models\User",
            'assigned',
            'user_module_assignments',
            'user_id',
            'assigned_id'
        );
    }

    public function assignedUsers()
    {
        return $this->morphedByMany(
            "\App\Models\User",
            'assigned',
            'user_module_assignments',
            'assigned_id',
            'user_id'
        )->wherePivot('purpose', 'manager');
    }
}