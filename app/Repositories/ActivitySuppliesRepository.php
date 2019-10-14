<?php


namespace App\Repositories;


use App\Models\ActivitySuppliesModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivitySuppliesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivitySuppliesModel $model)
    {
        $this->model = $model;
    }
}
            