<?php


namespace App\Repositories;


use App\Models\OaProcessNodeActionModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessNodeActionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessNodeActionModel $model)
    {
        $this->model = $model;
    }
}
            