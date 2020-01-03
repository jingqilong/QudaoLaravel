<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsSpecViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecViewModel $model)
    {
        $this->model = $model;
    }
}
            