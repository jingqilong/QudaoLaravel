<?php


namespace App\Repositories;


use App\Models\MedicalLabelModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalLabelRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalLabelModel $model)
    {
        $this->model = $model;
    }
}
            