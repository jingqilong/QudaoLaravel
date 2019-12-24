<?php


namespace App\Repositories;


use App\Models\OaProcessNodeActionsResultModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessNodeActionsResultRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessNodeActionsResultModel $model)
    {
        $this->model = $model;
    }
}
            