<?php


namespace App\Repositories;


use App\Models\ShopOrderRelateViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopOrderRelateViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderRelateViewModel $model)
    {
        $this->model = $model;
    }
}
            