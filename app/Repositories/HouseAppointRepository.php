<?php


namespace App\Repositories;


use App\Models\HouseAppointModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseAppointRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseAppointModel $model)
    {
        $this->model = $model;
    }
}
            