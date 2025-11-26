<?php

namespace App\Extensions\RBAC\System\Traits;

trait DataAccessModel
{
    /**
     * Begin querying the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryPermission($menuId)
    {
        /** @var \App\Extensions\RBAC\System\Repositories\AuthorizationRepository */
        $authrizationRepo = app(\App\Extensions\RBAC\System\Repositories\AuthorizationRepository::class);
        $authrizationRepo->checkingDataAccess(self::class, $menuId);
        
        if ($authrizationRepo->getAllDataAccessStatus()) {
            return parent::query();
        }

        $modelIds = $authrizationRepo->getModelIds();
        if (count($modelIds) > 0) {
            return parent::query()->whereIn('id', $modelIds);
        }

        return parent::query()->whereRaw('1 = 0');
    }
}