<?php


namespace App\Repositories;


use App\Models\MedicalHospitalModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalHospitalRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalHospitalModel $model)
    {
        $this->model = $model;
    }
}
            