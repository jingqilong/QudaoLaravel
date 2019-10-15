<?php


namespace App\Repositories;


use App\Models\ActivityWinningModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityWinningRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityWinningModel $model)
    {
        $this->model = $model;
    }
}
            