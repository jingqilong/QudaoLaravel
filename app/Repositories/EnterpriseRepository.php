<?php


namespace App\Repositories;


use App\Models\EnterpriseModel;
use App\Repositories\Traits\RepositoryTrait;

class EnterpriseRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EnterpriseModel $model)
    {
        $this->model = $model;
    }
}