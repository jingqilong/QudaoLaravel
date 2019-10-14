<?php


namespace App\Repositories;


use App\Models\ActivitySiteModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivitySiteRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivitySiteModel $model)
    {
        $this->model = $model;
    }
}
            