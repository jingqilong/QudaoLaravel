<?php


namespace App\Repositories;


use App\Models\ActivityLinksModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityLinksRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityLinksModel $model)
    {
        $this->model = $model;
    }
}
            