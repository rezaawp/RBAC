<?php

namespace App\Extensions\RBAC\System\Repositories;

use App\Models\Accounting;

interface PermissionRepository
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
     * Delete chat_room
     *
     * @param mixed $id archive classification access right id
     *
     * @return bool
     */
    public function delete($id): bool;

    public function getPermissionByRoles($role_ids = []);
    public function getPermissionActiveAndInactive($filters = []);
}
