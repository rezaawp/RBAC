<?php

namespace App\Extensions\RBAC\System\Models;

use App\Extensions\RBAC\System\Traits\DataAccessModel;

class Role extends \Spatie\Permission\Models\Role
{
    protected static $menuId = 204;
    use DataAccessModel;
}