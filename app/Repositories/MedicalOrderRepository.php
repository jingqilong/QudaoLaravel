<?php


namespace App\Repositories;


use App\Models\MedicalOrderModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalOrderRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalOrderModel $model)
    {
        $this->model = $model;
    }
}
            