<?php


namespace App\Repositories;


use App\Models\HouseTowardModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseTowardRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseTowardModel $model)
    {
        $this->model = $model;
    }
}
            