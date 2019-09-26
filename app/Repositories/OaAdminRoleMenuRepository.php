<?php


namespace App\Repositories;


use App\Models\OaAdminRoleMenuModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAdminRoleMenuRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminRoleMenuModel $model)
    {
        $this->model = $model;
    }
}
            