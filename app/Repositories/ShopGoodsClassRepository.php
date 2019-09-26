<?php


namespace App\Repositories;


use App\Models\ShopGoodsClassModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsClassRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsClassModel $model)
    {
        $this->model = $model;
    }
}
            