<?php


namespace App\Repositories;


use App\Models\MedicalEvaluationModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalEvaluationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalEvaluationModel $model)
    {
        $this->model = $model;
    }
}
            