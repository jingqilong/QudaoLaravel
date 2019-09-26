<?php


namespace App\Repositories;


use App\Models\ShopCartModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopCartRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopCartModel $model)
    {
        $this->model = $model;
    }
}
            