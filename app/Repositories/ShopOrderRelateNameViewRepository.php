<?php


namespace App\Repositories;


use App\Models\ShopOrderRelateNameViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopOrderRelateNameViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderRelateNameViewModel $model)
    {
        $this->model = $model;
    }
}
            