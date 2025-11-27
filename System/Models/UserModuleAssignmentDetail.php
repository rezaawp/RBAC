<?php

namespace App\Extensions\RBAC\System\Models;
use Illuminate\Database\Eloquent\Model;

class UserModuleAssignmentDetail extends Model
{
    protected $table = 'user_module_assignments';

    protected $fillable = [
        'user_id',
        'module_id',
        'model_type',
        'model_id',
    ];
    
    public function assigned()
    {
        return $this->morphTo();
    }
}
