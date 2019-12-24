<?php


namespace App\Repositories;


use App\Models\OaProcessActionsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionsModel $model)
    {
        $this->model = $model;
    }
}
            