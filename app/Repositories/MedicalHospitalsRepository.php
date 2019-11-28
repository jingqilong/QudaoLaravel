<?php


namespace App\Repositories;


use App\Models\MedicalHospitalsModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalHospitalsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalHospitalsModel $model)
    {
        $this->model = $model;
    }
}
            