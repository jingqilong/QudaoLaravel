<?php


namespace App\Repositories;


use App\Models\CommonExpressModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonExpressRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonExpressModel $model)
    {
        $this->model = $model;
    }
}
            