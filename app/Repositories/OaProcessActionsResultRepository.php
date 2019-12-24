<?php


namespace App\Repositories;


use App\Models\OaProcessActionsResultModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionsResultRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionsResultModel $model)
    {
        $this->model = $model;
    }
}
            