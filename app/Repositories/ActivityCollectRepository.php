<?php


namespace App\Repositories;


use App\Models\ActivityCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityCollectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityCollectModel $model)
    {
        $this->model = $model;
    }
}
            