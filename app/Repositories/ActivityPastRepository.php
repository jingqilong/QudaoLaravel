<?php


namespace App\Repositories;


use App\Models\ActivityPastModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityPastRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * ActivityPastModel constructor.
     * @param $model
     */
    public function __construct(ActivityPastModel $model)
    {
        $this->model = $model;
    }
}
            