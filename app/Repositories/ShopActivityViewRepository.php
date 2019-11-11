<?php


namespace App\Repositories;


use App\Models\ShopActivityViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopActivityViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopActivityViewModel $model)
    {
        $this->model = $model;
    }
}
            