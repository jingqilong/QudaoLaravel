<?php


namespace App\Repositories;


use App\Models\MedicalDoctorAuditModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDoctorAuditRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDoctorAuditModel $model)
    {
        $this->model = $model;
    }
}
            