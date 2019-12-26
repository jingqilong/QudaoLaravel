<?php


namespace App\Repositories;


use App\Models\OaProcessNodeActionsResultViewModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessNodeActionsResultViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessNodeActionsResultViewModel $model)
    {
        $this->model = $model;
    }
}
            