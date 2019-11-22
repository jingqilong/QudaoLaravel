<?php


namespace App\Repositories;


use App\Models\OaAdminRolePermissionsModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        #删除之前的权限关系，添加新的关系
        if (OaAdminRolePermissionsRepository::exists(['role_id' => $add_data['role_id']])){
            if (!OaAdminRolePermissionsRepository::delete(['role_id' => $add_data['role_id']])){
                DB::rollBack();
                return false;
            }
        }
        $arr = [];
        foreach ($add_data['permission_ids'] as $k => $permission_id){
            $arr[$k]['role_id']       = $add_data['role_id'];
            $arr[$k]['permission_id'] = $permission_id;
            $arr[$k]['created_at']    = date('Y-m-d H:m:s');
            $arr[$k]['updated_at']    = date('Y-m-d H:m:s');
        }
        if (!$this->create($arr)){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 获取权限相关的权限ID
     * @param $role_id
     * @param bool $string_or_array     true为字符串，false为数组
     * @return array|string
     */
    protected function getPermissionIds($role_id, $string_or_array = true)
    {
        if (!$list = $this->getList(['role_id' => $role_id])){
            return $string_or_array ? '' : [];
        }
        $permission_ids = array_column($list,'permission_id');
        if (!$string_or_array){
            return $permission_ids;
        }
        return implode($permission_ids,',');
    }
}
            