<?php


namespace App\Repositories;


use App\Models\EventGuestModel;
use App\Repositories\Traits\RepositoryTrait;

class EventGuestRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(EventGuestModel $model)
    {
        $this->model = $model;
    }
}
            