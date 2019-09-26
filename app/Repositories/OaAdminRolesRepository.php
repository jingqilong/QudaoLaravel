<?php


namespace App\Repositories;


use App\Models\OaAdminRolesModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAdminRolesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminRolesModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加角色
     * @param array $add_roles
     * @return integer|null
     */
    protected function createRoles(array $add_roles)
    {
        $arr = [
            'name' => $add_roles['name'],
            'slug' => $add_roles['slug'],
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ];
        return $this->getAddId($arr);
    }
}
            