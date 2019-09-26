<?php


namespace App\Repositories;


use App\Models\EventImagesModel;
use App\Repositories\Traits\RepositoryTrait;

class EventImagesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventImagesModel $model)
    {
        $this->model = $model;
    }
}
            