<?php


namespace App\Repositories;


use App\Models\PrimeProjectModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeProjectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeProjectModel $model)
    {
        $this->model = $model;
    }
}
            