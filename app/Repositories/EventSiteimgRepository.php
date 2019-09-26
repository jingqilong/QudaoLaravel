<?php


namespace App\Repositories;


use App\Models\EventSiteimgModel;
use App\Repositories\Traits\RepositoryTrait;

class EventSiteimgRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventSiteimgModel $model)
    {
        $this->model = $model;
    }
}
            