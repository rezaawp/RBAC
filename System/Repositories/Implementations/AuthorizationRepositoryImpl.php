<?php

namespace App\Extensions\RBAC\System\Repositories\Implementations;

use App\Extensions\RBAC\System\Exception\AuthorizationError;
use App\Extensions\RBAC\System\Models\UserModuleAssignmentDetail;
use App\Extensions\RBAC\System\Models\UserModuleAssignmentHeader;
use App\Extensions\RBAC\System\Repositories\AuthorizationRepository;
use App\Extensions\RBAC\System\Repositories\RoleRepository;
use Illuminate\Http\Response;

class AuthorizationRepositoryImpl implements AuthorizationRepository
{
    private bool $status = false;
    private bool $allDataAccess = false;
    private array $modelIds = [];
    private $authorizeStatus;
    private $latestPermission;
    private UserModuleAssignmentHeader $modulHeader;
    private UserModuleAssignmentDetail $modulDetail;

    private RoleRepository $roleRepository;

    function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    function setModulHeader(UserModuleAssignmentHeader $modulHeader): void {
        $this->modulHeader = $modulHeader;
    }

    function getModulHeader(): UserModuleAssignmentHeader {
        return $this->modulHeader;
    }

    function setModulDetail(UserModuleAssignmentDetail $modulDetail): void {
        $this->modulDetail = $modulDetail;
    }

    function getModulDetail(): UserModuleAssignmentDetail {
        return $this->modulDetail;
    }

    public function checkingDataAccess($modelType, $idmodul): self
    {
        $isAllDataAccess = UserModuleAssignmentHeader::query()
            ->where('module_id', $idmodul)
            ->where('user_id', auth()->id())
            ->first();
        
        if (! $isAllDataAccess) {
            throw new AuthorizationError('Module header not found for the user. Please check data and contact to administrator.');
        }

        $this->setModulHeader($isAllDataAccess);

        $modulDetail = new UserModuleAssignmentDetail();
        $modulDetail->model_type = $modelType;
        $this->setModulDetail($modulDetail);

        if ($isAllDataAccess && $isAllDataAccess->all_data_access == 1) {
            $this->status = true;
            $this->allDataAccess = true;
            $this->modelIds = [];
            return $this;
        } elseif (!$isAllDataAccess) {
            $this->status = false;
            $this->allDataAccess = true;
            $this->modelIds = [];
            return $this;
        }

        $modelIds = UserModuleAssignmentDetail::query()
            ->where('purpose', 'data-access')
            ->where('model_type', $modelType)
            ->where('module_id', $idmodul)
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($item) {
                return $item->model_id;
            })->toArray();

        $this->status = true;
        $this->allDataAccess = false;
        $this->modelIds = $modelIds;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getAllDataAccessStatus(): bool
    {
        return $this->allDataAccess;
    }

    public function getModelIds(): array
    {
        return $this->modelIds;
    }

    function authorize($permission, $idmodul): self
    {
        $permission = (string) $idmodul . '__' . (string) $permission;
        $this->latestPermission = $permission;
        $this->authorizeStatus = $this->setAuthorizeStatus($permission, $idmodul);
        return $this;
    }

    function setAuthorizeStatus($permission, $idmodul): bool
    {
        if ($this->roleRepository->userHasPermission($permission, (string) $idmodul)
        && $this->getStatus()) {
            return true;
        } else {
            throw new \Spatie\Permission\Exceptions\UnauthorizedException(Response::HTTP_FORBIDDEN, 'User does not have the right permissions.');
        }
    }

    function limitedDataAccess(): bool
    {
        return $this->authorizeStatus && $this->getAllDataAccessStatus() == false;
    }

    function fullDataAccess(): bool
    {
        return $this->authorizeStatus && $this->getAllDataAccessStatus() == true;
    }

    function getLatestPermission(): string|null
    {
        return $this->latestPermission;
    }

    function addDataAccess(array $ids): void {
        if (empty($this->getModulHeader()->user_id)) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException('Cannot add data access because the header module is not yet available');
        }

        $data = [];
        $userId = auth()->user()->id;
        $moduleId = $this->getModulHeader()->module_id;
        $modelType = $this->getModulDetail()->model_type;
        $headerId = $this->getModulHeader()->id;

        foreach ($ids as $id) {
            $data[] = [
                'user_id' => $userId,
                'module_id' => $moduleId,
                'model_type' => $modelType,
                'model_id' => $id,
                'header_id' => $headerId
            ];
        }

        if (!empty($data)) {
            UserModuleAssignmentDetail::query()->insert($data);
        }
    }
}
