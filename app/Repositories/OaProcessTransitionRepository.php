<?php


namespace App\Repositories;


use App\Models\OaProcessTransitionModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessTransitionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessTransitionModel $model)
    {
        $this->model = $model;
    }
}
            