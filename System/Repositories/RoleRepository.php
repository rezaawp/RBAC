<?php

namespace App\Extensions\RBAC\System\Repositories;

use Spatie\Permission\Models\Role;

interface RoleRepository
{
    /**
     * Get all project
     *
     * @param array $filters key-value array for filtering data
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function findAll($filters = []);

    /**
     * Get specific project by id
     *
     * @param mixed $id chat_room id
     *
     * @return \App\Models\Role
     */
    public function findById($id): Role;

    /**
     * Create project
     *
     * @param array $data valid data for creating chat_room
     *
     * @return \App\Models\Role
     */
    public function create(array $data): Role;

    /**
     * Update project
     *
     * @param mixed $id chat_room id
     * @param array $data valid data for updating chat_room
     *
     * @return \App\Models\Role
     */
    public function update($id, array $data): Role;

    /**
     * Delete chat_room
     *
     * @param mixed $id archive classification access right id
     *
     * @return bool
     */
    public function delete($id): bool;

    public function givePermissions($role_id, array|string $permissions): bool;

    public function assignRoleToUser($user_id, $role_key);

    public function removePermissiomFromRole($role_id, array|string $permissions); // https://spatie.be/docs/laravel-permission/v6/basic-usage/basic-usage#content-remove-permission-from-a-role
    public function findAllPermission($role_id); // https://spatie.be/docs/laravel-permission/v6/basic-usage/role-permissions#content-what-permissions-does-a-role-have

    public function getAllUserByRole($id);
    public function userHasPermission($permission, $guardName = null): bool; // https://spatie.be/docs/laravel-permission/v6/basic-usage/direct-permissions
    public function myRolesAuth();
    public function findWithPermissions($filters = []);
    function syncPermissionsToTole($request): bool;
}
