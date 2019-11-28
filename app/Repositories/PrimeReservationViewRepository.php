<?php


namespace App\Repositories;


use App\Models\PrimeReservationViewModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeReservationViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeReservationViewModel $model)
    {
        $this->model = $model;
    }
}
            