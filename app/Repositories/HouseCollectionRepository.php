<?php


namespace App\Repositories;


use App\Models\HouseCollectionModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseCollectionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseCollectionModel $model)
    {
        $this->model = $model;
    }
}
            