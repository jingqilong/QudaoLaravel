<?php


namespace App\Repositories;


use App\Models\TpAdminLoginLogModel;
use App\Repositories\Traits\RepositoryTrait;

class TpAdminLoginLogRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(TpAdminLoginLogModel $model)
    {
        $this->model = $model;
    }
}
            