<?php

namespace App\Extensions\RBAC\System\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class UserModuleAssignmentHeader extends Model
{
    protected $table = 'user_module_assignments_header';

    protected $guarded = ['id'];

    function details(): HasMany {
        return $this->hasMany(UserModuleAssignmentDetail::class, 'header_id', 'id');
    }
}
