<?php


namespace App\Repositories;


use App\Models\ShopOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderModel $model)
    {
        $this->model = $model;
    }
}
            