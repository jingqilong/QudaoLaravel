<?php


namespace App\Repositories;


use App\Models\PrimeReservationModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeReservationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeReservationModel $model)
    {
        $this->model = $model;
    }
}
            