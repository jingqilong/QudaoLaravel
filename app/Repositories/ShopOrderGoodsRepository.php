<?php


namespace App\Repositories;


use App\Models\ShopOrderGoodsModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopOrderGoodsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderGoodsModel $model)
    {
        $this->model = $model;
    }
}
            