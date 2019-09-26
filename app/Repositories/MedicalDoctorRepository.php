<?php


namespace App\Repositories;


use App\Models\MedicalDoctorModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDoctorRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDoctorModel $model)
    {
        $this->model = $model;
    }
}
            