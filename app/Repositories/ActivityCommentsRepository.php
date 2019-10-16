<?php


namespace App\Repositories;


use App\Models\ActivityCommentsModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityCommentsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityCommentsModel $model)
    {
        $this->model = $model;
    }
}
            