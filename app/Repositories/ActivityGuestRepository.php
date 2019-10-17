<?php


namespace App\Repositories;


use App\Models\ActivityGuestModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityGuestRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityGuestModel $model)
    {
        $this->model = $model;
    }
}
            