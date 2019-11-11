<?php


namespace App\Repositories;


use App\Models\ShopOrderRelateModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopOrderRelateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderRelateModel $model)
    {
        $this->model = $model;
    }
}
            