<?php


namespace App\Repositories;


use App\Models\CommonPvModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonPvRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonPvModel $model)
    {
        $this->model = $model;
    }
}
            