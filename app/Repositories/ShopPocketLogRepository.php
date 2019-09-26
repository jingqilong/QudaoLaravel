<?php


namespace App\Repositories;


use App\Models\ShopPocketLogModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopPocketLogRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopPocketLogModel $model)
    {
        $this->model = $model;
    }
}
            