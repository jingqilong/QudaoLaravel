<?php


namespace App\Repositories;


use App\Models\MediclaHospitalsModel;
use App\Repositories\Traits\RepositoryTrait;

class MediclaHospitalsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MediclaHospitalsModel $model)
    {
        $this->model = $model;
    }
}
            