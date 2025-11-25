<?php

namespace App\Extensions\RBAC\System\Http\Controllers;

use App\Extensions\RBAC\System\Repositories\RoleRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller {
    private RoleRepository $roleRepository;
    
    function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }
    
    function index(Request $request) {
        return view("rbac::role.index", [
            'items' => $this->roleRepository->findAll($request->all()),
        ]);
    }

    function store(Request $request) {
        $request->validate([
            'role' => 'required|string|unique:roles,name',
        ]);

        $role = $this->roleRepository->create([
            'name' => $request->input('role'),
        ]);

        return redirect()->route('dashboard.admin.rbac.roles.index')
            ->with(['message' => __('Role created successfully.'), 'type' => 'success']);
    }

    function destroy($id) {
        $this->roleRepository->delete($id);

        return redirect()->route('dashboard.admin.rbac.roles.index')
            ->with(['message' => __('Role deleted successfully.'), 'type' => 'success']);
    }

    function rolePermissionSave(Request $request) {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $success = $this->roleRepository->syncPermissionsToTole($request);

        if ($success) {
            return redirect()->route('dashboard.admin.rbac.roles.edit', ['role' => $request->input('role_id')])
                ->with(['message' => __('Permissions updated successfully.'), 'type' => 'success']);
        } else {
            return redirect()->route('dashboard.admin.rbac.roles.edit', ['role' => $request->input('role_id')])
                ->with(['message' => __('Failed to update permissions.'), 'type' => 'error']);
        }
    }

    function edit(Request $request, $id) {
        $role = Role::findById($id);
        $permissions = $role->permissions->pluck('name')->toArray();
        
        return view("rbac::role.edit", [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }
}