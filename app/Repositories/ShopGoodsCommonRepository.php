<?php


namespace App\Repositories;


use App\Models\ShopGoodsCommonModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsCommonRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsCommonModel $model)
    {
        $this->model = $model;
    }
}
            