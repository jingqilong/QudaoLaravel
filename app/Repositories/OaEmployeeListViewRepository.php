<?php


namespace App\Repositories;


use App\Models\OaEmployeeListViewModel;
use App\Repositories\Traits\RepositoryTrait;

class OaEmployeeListViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaEmployeeListViewModel $model)
    {
        $this->model = $model;
    }
}
            