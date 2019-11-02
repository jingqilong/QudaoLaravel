<?php


namespace App\Repositories;


use App\Models\MedicalOrdersModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalOrdersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalOrdersModel $model)
    {
        $this->model = $model;
    }
}
            