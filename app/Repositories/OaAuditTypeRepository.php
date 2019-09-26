<?php


namespace App\Repositories;


use App\Models\OaAuditTypeModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAuditTypeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAuditTypeModel $model)
    {
        $this->model = $model;
    }
}
            