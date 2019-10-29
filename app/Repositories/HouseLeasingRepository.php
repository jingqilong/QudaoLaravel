<?php


namespace App\Repositories;


use App\Models\HouseLeasingModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseLeasingRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseLeasingModel $model)
    {
        $this->model = $model;
    }
}
            