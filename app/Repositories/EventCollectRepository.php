<?php


namespace App\Repositories;


use App\Models\EventCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class EventCollectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventCollectModel $model)
    {
        $this->model = $model;
    }
}
            