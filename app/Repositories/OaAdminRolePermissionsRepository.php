<?php


namespace App\Repositories;


use App\Models\OaAdminRolePermissionsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAdminRolePermissionsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminRolePermissionsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 批量添加角色与权限的对应关系
     * @param array $add_data
     * @return bool|null
     */
    protected function createRelate(array $add_data)
    {
        if (empty($add_data)){
            return false;
        }
        $arr = [];
        foreach ($add_data['permission_ids'] as $k => $permission_id){
            $arr[$k]['role_id']       = $add_data['role_id'];
            $arr[$k]['permission_id'] = $permission_id;
            $arr[$k]['created_at']    = date('Y-m-d H:m:s');
            $arr[$k]['updated_at']    = date('Y-m-d H:m:s');
        }
        return $this->create($arr);
    }
}
            