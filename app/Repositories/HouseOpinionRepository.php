<?php


namespace App\Repositories;


use App\Models\HouseOpinionModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseOpinionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseOpinionModel $model)
    {
        $this->model = $model;
    }
}
            