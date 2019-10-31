<?php


namespace App\Repositories;


use App\Models\MedicalDoctorsModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDoctorsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDoctorsModel $model)
    {
        $this->model = $model;
    }
}
            