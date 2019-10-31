<?php


namespace App\Repositories;


use App\Models\MedicalReservationModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalReservationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalReservationModel $model)
    {
        $this->model = $model;
    }
}
            