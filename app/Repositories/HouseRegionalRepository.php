<?php


namespace App\Repositories;


use App\Models\HouseRegionalModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseRegionalRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseRegionalModel $model)
    {
        $this->model = $model;
    }
}
            