<?php


namespace App\Repositories;


use App\Models\ActivityHostsModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityHostsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityHostsModel $model)
    {
        $this->model = $model;
    }
}
            