<?php


namespace App\Repositories;


use App\Models\OaAdminOperationLogModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAdminOperationLogRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminOperationLogModel $model)
    {
        $this->model = $model;
    }
}
            