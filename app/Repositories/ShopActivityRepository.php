<?php


namespace App\Repositories;


use App\Models\ShopActivityModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopActivityRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopActivityModel $model)
    {
        $this->model = $model;
    }
}
            