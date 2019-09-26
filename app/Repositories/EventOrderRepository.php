<?php


namespace App\Repositories;


use App\Models\EventOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class EventOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventOrderModel $model)
    {
        $this->model = $model;
    }
}
            