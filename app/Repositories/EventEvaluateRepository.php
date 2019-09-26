<?php


namespace App\Repositories;


use App\Models\EventEvaluateModel;
use App\Repositories\Traits\RepositoryTrait;

class EventEvaluateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventEvaluateModel $model)
    {
        $this->model = $model;
    }
}
            