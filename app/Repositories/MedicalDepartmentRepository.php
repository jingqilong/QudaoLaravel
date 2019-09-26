<?php


namespace App\Repositories;


use App\Models\MedicalDepartmentModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDepartmentRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDepartmentModel $model)
    {
        $this->model = $model;
    }
}
            