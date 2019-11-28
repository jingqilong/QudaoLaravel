<?php


namespace App\Repositories;


use App\Models\ShopGoodsCategoryModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsCategoryRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsCategoryModel $model)
    {
        $this->model = $model;
    }
}
            