<?php


namespace App\Repositories;


use App\Models\MedicalDepartmentsModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDepartmentsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDepartmentsModel $model)
    {
        $this->model = $model;
    }
}
            