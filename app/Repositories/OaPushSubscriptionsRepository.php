<?php


namespace App\Repositories;


use App\Models\OaPushSubscriptionsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaPushSubscriptionsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaPushSubscriptionsModel $model)
    {
        $this->model = $model;
    }
}
            