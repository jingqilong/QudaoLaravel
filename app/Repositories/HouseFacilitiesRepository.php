<?php


namespace App\Repositories;


use App\Models\HouseFacilitiesModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseFacilitiesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseFacilitiesModel $model)
    {
        $this->model = $model;
    }
}
            