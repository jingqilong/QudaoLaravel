<?php


namespace App\Repositories;


use App\Models\OaProcessNodeModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessNodeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessNodeModel $model)
    {
        $this->model = $model;
    }
}
            