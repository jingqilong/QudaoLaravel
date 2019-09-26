<?php


namespace App\Repositories;


use App\Models\ShopCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopCollectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopCollectModel $model)
    {
        $this->model = $model;
    }
}
            