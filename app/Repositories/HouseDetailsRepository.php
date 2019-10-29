<?php


namespace App\Repositories;


use App\Models\HouseDetailsModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseDetailsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseDetailsModel $model)
    {
        $this->model = $model;
    }
}
            