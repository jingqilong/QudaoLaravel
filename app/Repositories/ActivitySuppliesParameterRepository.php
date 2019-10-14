<?php


namespace App\Repositories;


use App\Models\ActivitySuppliesParameterModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivitySuppliesParameterRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivitySuppliesParameterModel $model)
    {
        $this->model = $model;
    }
}
            