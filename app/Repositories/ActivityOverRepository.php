<?php


namespace App\Repositories;


use App\Models\ActivityOverModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityOverRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * ActivityOverModel constructor.
     * @param $model
     */
    public function __construct(ActivityOverModel $model)
    {
        $this->model = $model;
    }
}
            