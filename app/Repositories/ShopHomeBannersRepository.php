<?php


namespace App\Repositories;


use App\Models\ShopHomeBannersModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopHomeBannersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopHomeBannersModel $model)
    {
        $this->model = $model;
    }
}
            