<?php


namespace App\Repositories;


use App\Models\MedicalDoctorLablesModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDoctorLablesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDoctorLablesModel $model)
    {
        $this->model = $model;
    }
}
            