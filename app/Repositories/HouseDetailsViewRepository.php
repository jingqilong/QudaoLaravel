<?php


namespace App\Repositories;


use App\Models\HouseDetailsViewModel;
use App\Repositories\Traits\RepositoryTrait;

class HouseDetailsViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseDetailsViewModel $model)
    {
        $this->model = $model;
    }
}
            