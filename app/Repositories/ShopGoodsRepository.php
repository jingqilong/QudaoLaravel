<?php


namespace App\Repositories;


use App\Models\ShopGoodsModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsModel $model)
    {
        $this->model = $model;
    }
}
            