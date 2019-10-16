<?php


namespace App\Repositories;


use App\Models\ActivityRegisterModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityRegisterRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityRegisterModel $model)
    {
        $this->model = $model;
    }
}
            