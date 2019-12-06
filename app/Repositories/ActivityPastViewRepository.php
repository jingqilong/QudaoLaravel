<?php


namespace App\Repositories;


use App\Models\ActivityPastViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityPastViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * ActivityPastModel constructor.
     * @param $model
     */
    public function __construct(ActivityPastViewModel $model)
    {
        $this->model = $model;
    }
}
            