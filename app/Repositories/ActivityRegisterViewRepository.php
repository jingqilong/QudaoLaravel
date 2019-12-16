<?php


namespace App\Repositories;


use App\Models\ActivityRegisterViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityRegisterViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityRegisterViewModel $model)
    {
        $this->model = $model;
    }
}
            