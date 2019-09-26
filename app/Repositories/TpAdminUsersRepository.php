<?php


namespace App\Repositories;


use App\Models\TpAdminUsersModel;
use App\Repositories\Traits\RepositoryTrait;

class TpAdminUsersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(TpAdminUsersModel $model)
    {
        $this->model = $model;
    }
}
            