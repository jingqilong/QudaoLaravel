<?php


namespace App\Repositories;


use App\Models\HouseUnitModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseUnitRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseUnitModel $model)
    {
        $this->model = $model;
    }
}
            