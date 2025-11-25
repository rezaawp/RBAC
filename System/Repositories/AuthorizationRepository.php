<?php

namespace App\Extensions\RBAC\System\Repositories;

use App\Extensions\RBAC\System\Models\UserModuleAssignmentDetail;
use App\Extensions\RBAC\System\Models\UserModuleAssignmentHeader;

interface AuthorizationRepository
{
    /**
     * Mengecek data akses user pada modul tertentu.
     *
     * @param string $modelType
     * @param int $idmodul
     * @return array
     */
    public function checkingDataAccess($modelType, $idmodul): self;
    public function getStatus(): bool;
    public function getAllDataAccessStatus(): bool;
    public function getModelIds(): array;
    function authorize($permission, $idmodul): self;
    function limitedDataAccess(): bool;
    function fullDataAccess(): bool;
    function getLatestPermission(): string|null;
    function setModulHeader(UserModuleAssignmentHeader $modulHeader): void;
    function getModulHeader(): UserModuleAssignmentHeader;
    function setModulDetail(UserModuleAssignmentDetail $modulDetail): void;
    function getModulDetail(): UserModuleAssignmentDetail;
    function addDataAccess(array $ids): void;
}
