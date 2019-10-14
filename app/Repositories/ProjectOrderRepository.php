<?php


namespace App\Repositories;


use App\Models\ProjectOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class ProjectOrderRepository extends ApiRepository
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
            