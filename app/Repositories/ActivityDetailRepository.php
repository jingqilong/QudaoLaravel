<?php


namespace App\Repositories;


use App\Models\ActivityDetailModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityDetailRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityDetailModel $model)
    {
        $this->model = $model;
    }
}
            