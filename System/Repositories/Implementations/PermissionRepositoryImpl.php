<?php

namespace App\Extensions\RBAC\System\Repositories\Implementations;

use App\Extensions\RBAC\System\Repositories\PermissionRepository;
use App\Extensions\RBAC\System\Repositories\RoleRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PermissionRepositoryImpl implements PermissionRepository
{
    /**
     * {@inheritdoc}
     */

    private RoleRepository $roleRepository;
    function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }
    public function findAll($filters = [])
    {
        $perPage = config('app.pagination_per_page');
        $filters = collect($filters);

        $query = Permission::all();
        if ($filters->has('group_by')) {
            return $query->groupBy('modul_id');
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id): bool
    {
        $accounting = $this->findById($id);

        if ($accounting->count() < 0) {
            throw new UnprocessableEntityHttpException('Accounting not found');
        }

        return $accounting->delete();
    }

    function getPermissionByRoles($role_ids = [])
    {
        return Role::whereIn('id', $role_ids)->with('permissions')->get()->collect()->map(function ($item) {
            return collect($item->permissions)->map(function ($permission) {
                return collect($permission)->only('name');
            });
        })->collapse()->flatten();
    }

    function getPermissionActiveAndInactive($filters = [])
    {
        $filters = collect($filters);

        $all_permissions = $this->findAll()->pluck('name');
        $all_permissions_grouping = $this->findAll(['group_by' => true])->toArray();

        foreach ($all_permissions_grouping as $feature => $permissions) {
            $new_data = collect($permissions)->pluck('name')->map(function ($item) {
                return [$item => false];
            })->collapse();
            $all_permissions_grouping[$feature] = $new_data;
        }

        $all_roles = $this->roleRepository->findWithPermissions();
        if ($filters->has('role_id')) {
            $all_roles = $this->roleRepository->findWithPermissions([
                'role_id' => $filters->get('role_id')
            ]);
        }

        $all_roles = $all_roles->map(function ($item) use ($all_permissions, $all_permissions_grouping) {
            $new_all_permission_group = collect($all_permissions_grouping)->toArray();
            $all_permissions_2 = collect($all_permissions)->toArray();
            $data_permissions = collect($item->permissions)->pluck('name')->toArray();
            $active_permissions = []; // ini adalah permission active

            foreach ($data_permissions as $permission) {
                $index_active = array_search($permission, $all_permissions_2);
                unset ($all_permissions_2[$index_active]);
                array_push($active_permissions, [
                    $permission => true
                ]);
            }

            $active_permissions = collect($active_permissions)->collapse();
            $inactive_permissions = collect($all_permissions_2)->map(function ($permission_inactive) {
                return [
                    $permission_inactive => false
                ];
            })->collapse();

            $result_permissions = $active_permissions->merge($inactive_permissions);
            foreach ($new_all_permission_group as $feature => $p_names) {
                foreach ($p_names as $key_p_name => $p_bool) {
                    foreach ($result_permissions as $key_permission_result => $bool) {
                        if ($key_permission_result === $key_p_name) {
                            [$modul, $key_permission] = array_pad(explode('__', $key_permission_result), 2, null);
                            unset($new_all_permission_group[$feature][$key_permission_result]);
                            $new_all_permission_group[$feature][$key_permission] = $bool;
                            break;
                        }
                    }
                }
            }
            return [
                'id' => $item->id,
                'name' => $item->name,
                // 'permissions' => $result_permissions,
                'permissions' => $new_all_permission_group
            ];
        });

        return $all_roles;
    }

    /** modul */
}
