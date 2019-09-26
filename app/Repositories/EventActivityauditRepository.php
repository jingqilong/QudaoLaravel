<?php


namespace App\Repositories;


use App\Models\EventActivityauditModel;
use App\Repositories\Traits\RepositoryTrait;

class EventActivityauditRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventActivityauditModel $model)
    {
        $this->model = $model;
    }
}
            