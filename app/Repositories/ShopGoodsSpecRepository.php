<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsSpecRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecModel $model)
    {
        $this->model = $model;
    }
}
            