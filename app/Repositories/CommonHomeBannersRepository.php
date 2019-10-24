<?php


namespace App\Repositories;


use App\Models\CommonHomeBannersModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonHomeBannersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonHomeBannersModel $model)
    {
        $this->model = $model;
    }
}
            