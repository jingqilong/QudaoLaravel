<?php


namespace App\Repositories;


use App\Models\EventActivitysModel;
use App\Repositories\Traits\RepositoryTrait;

class EventActivitysRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventActivitysModel $model)
    {
        $this->model = $model;
    }
}
            