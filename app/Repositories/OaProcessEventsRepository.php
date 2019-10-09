<?php


namespace App\Repositories;


use App\Models\OaProcessEventsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessEventsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessEventsModel $model)
    {
        $this->model = $model;
    }
}
            