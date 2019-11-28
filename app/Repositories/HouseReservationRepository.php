<?php


namespace App\Repositories;


use App\Models\HouseReservationModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseReservationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseReservationModel $model)
    {
        $this->model = $model;
    }
}
            