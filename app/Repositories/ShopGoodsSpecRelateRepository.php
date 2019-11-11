<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecRelateModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsSpecRelateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecRelateModel $model)
    {
        $this->model = $model;
    }
}
            