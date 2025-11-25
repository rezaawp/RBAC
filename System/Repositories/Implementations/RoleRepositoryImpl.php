<?php

namespace App\Extensions\RBAC\System\Repositories\Implementations;

use App\Extensions\RBAC\System\Repositories\RoleRepository;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RoleRepositoryImpl implements RoleRepository
{
    /**
     * {@inheritdoc}
     */
    public function findAll($filters = [])
    {
        $perPage = config('app.pagination_per_page', 15);
        $filters = collect($filters);

        if ($filters->has('per_page')) {
            $perPage = (int) $filters->get('per_page');
        }

        $query = Role::query();

        if ($filters->has('search')) {
            $query->where('name', 'LIKE', '%' . $filters->get('search') . '%');
        }

        if ($filters->has('role')) {
            $query->where('parent_role', $filters->get('role'));
        }

        $query->orderBy('created_at', 'DESC');

        $query->with('permissions')->orderBy('created_at', 'DESC');

       $paginated = $query->paginate((int) $perPage);

        // transform each Role model to include permissions as comma-separated string
       $paginated->getCollection()->transform(function ($role) {
            // create/override attribute 'permissions' as CSV of permission names
            $role->permissions = $role->permissions->pluck('name')->implode(',');
            return $role;
        });

      return $paginated;

        // return $query->get();
        // return $query->paginate((int) $perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id): Role
    {
        $role = Role::findOrFail($id);

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data): Role
    {
        $role = $this->findById($id);
        if ($role->count() < 0) {
            throw new UnprocessableEntityHttpException('Role not found');
        }

        $role->forceFill($data)->save();

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id): bool
    {
        $role = $this->findById($id);

        if ($role->count() < 0) {
            throw new UnprocessableEntityHttpException('Role not found');
        }

        return $role->delete();
    }

    public function givePermissions($role_id, array|string $permissions): bool
    {
        try {
            $role = Role::findById($role_id);
            $role->givePermissionTo($permissions);

            return true;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            return false;
        }

    }

    public function assignRoleToUser($user_id, $role_key)
    {
        try {
            $user = User::find($user_id);
            $user->assignRole($role_key);
            return true;

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }

    public function removePermissiomFromRole($role_id, array|string $permissions)
    {
        // $permission = Permission::findByName($permissions);
        $role = Role::findById($role_id);


        $role->revokePermissionTo($permissions);
        // return $role;
        // $permission->removeRole($role);
    }

    public function findAllPermission($role_id)
    {
        $role = Role::findById($role_id);

        return $role->permissions;
    }

    public function getAllUserByRole($id)
    {
        return Role::findById($id)->users()->get();
    }

    public function userHasPermission($permission, $guardName = null): bool
    {
        if (!config('permission.use_permission_app')) {
            return true;
        }

        return auth()->user()->hasPermissionTo($permission);
    }

    function myRolesAuth()
    {
        return auth()->user()->getRoleNames();
    }

    function findWithPermissions($filters = [])
    {
        $filters = collect($filters);

        $query = Role::query()->with('permissions');
        $data = null;
        if ($filters->has('role_id')) {
            $data =  $query->where('id', $filters->get('role_id'))->get();
        }

        $data = $query->get();
        return $data;
    }

    function syncPermissionsToTole($request): bool
    {
        $permissions = $request->input('permissionItems', []);
        $id = $request->input('role_id');

        $role = Role::findById($id);

        $role->syncPermissions($permissions);

        return true;
    }
}
