<?php


namespace App\Repositories;


use App\Models\HouseEstateModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseEstateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseEstateModel $model)
    {
        $this->model = $model;
    }
}
            