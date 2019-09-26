<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecClassModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsSpecClassRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecClassModel $model)
    {
        $this->model = $model;
    }
}
            