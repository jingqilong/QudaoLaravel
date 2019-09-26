<?php


namespace App\Repositories;


use App\Models\OaAuditFlowModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAuditFlowRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAuditFlowModel $model)
    {
        $this->model = $model;
    }
}
            