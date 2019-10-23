<?php


namespace App\Repositories;


use App\Models\ProjectOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProjectOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ProjectOrderModel $model)
    {
        $this->model = $model;
    }
}
            