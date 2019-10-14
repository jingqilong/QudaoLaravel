<?php


namespace App\Repositories;


use App\Models\EnterpriseOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class EnterpriseOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EnterpriseOrderModel $model)
    {
        $this->model = $model;
    }
}