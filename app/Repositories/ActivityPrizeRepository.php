<?php


namespace App\Repositories;


use App\Models\ActivityPrizeModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityPrizeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityPrizeModel $model)
    {
        $this->model = $model;
    }
}
            