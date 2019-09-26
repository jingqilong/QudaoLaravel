<?php


namespace App\Repositories;


use App\Models\PrimeOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeOrderModel $model)
    {
        $this->model = $model;
    }
}
            