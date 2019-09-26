<?php


namespace App\Repositories;


use App\Models\EventActivityModel;
use App\Repositories\Traits\RepositoryTrait;

class EventActivityRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventActivityModel $model)
    {
        $this->model = $model;
    }
}
            