<?php


namespace App\Repositories;


use App\Models\CommonAreaModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonAreaRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonAreaModel $model)
    {
        $this->model = $model;
    }
}
            