<?php


namespace App\Repositories;


use App\Models\ShopCategoryModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopCategoryRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopCategoryModel $model)
    {
        $this->model = $model;
    }
}
            