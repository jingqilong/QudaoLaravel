<?php


namespace App\Repositories;


use App\Models\ShopEvaluateModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopEvaluateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopEvaluateModel $model)
    {
        $this->model = $model;
    }
}
            