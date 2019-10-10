<?php


namespace App\Repositories;


use App\Models\OaProcessTransitionDetailViewModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessTransitionDetailViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessTransitionDetailViewModel $model)
    {
        $this->model = $model;
    }
}
            