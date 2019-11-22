<?php


namespace App\Repositories;


use App\Models\MedicalOrdersViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalOrdersViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalOrdersViewModel $model)
    {
        $this->model = $model;
    }
}
            