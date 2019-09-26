<?php


namespace App\Repositories;


use App\Models\ShopAddressModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopAddressRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopAddressModel $model)
    {
        $this->model = $model;
    }
}
            