<?php


namespace App\Repositories;


use App\Models\MedicalDoctorLabelsModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDoctorLabelsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDoctorLabelsModel $model)
    {
        $this->model = $model;
    }
}
            