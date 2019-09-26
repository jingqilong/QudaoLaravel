<?php


namespace App\Repositories;


use App\Models\HouseOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseOrderModel $model)
    {
        $this->model = $model;
    }
}
            