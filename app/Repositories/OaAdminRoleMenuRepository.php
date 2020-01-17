<?php


namespace App\Repositories;


use App\Models\OaAdminRoleMenuModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\DB;

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

    /**
     * 批量添加角色与菜单的对应关系
     * @param array $add_data
     * @return bool|null
     */
    protected function createRelate(array $add_data)
    {
        if (empty($add_data)){
            return false;
        }
        DB::beginTransaction();
        #删除之前的菜单关系，添加新的关系
        if (OaAdminRoleMenuRepository::exists(['role_id' => $add_data['role_id']])){
            if (!OaAdminRoleMenuRepository::delete(['role_id' => $add_data['role_id']])){
                DB::rollBack();
                return false;
            }
        }
        if (empty($add_data['menu_ids'])){
            DB::commit();
            return true;
        }
        $arr = [];
        foreach ($add_data['menu_ids'] as $k => $menu_id){
            $arr[$k]['role_id']       = $add_data['role_id'];
            $arr[$k]['menu_id']       = $menu_id;
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
     * 获取权限相关的菜单ID
     * @param $role_id
     * @param bool $string_or_array     true为字符串，false为数组
     * @return array|string
     */
    protected function getMenuIds($role_id, $string_or_array = true){
        if (!$list = $this->getAllList(['role_id' => $role_id])){
            return $string_or_array ? '' : [];
        }
        $menu_ids = array_column($list,'menu_id');
        if (!$string_or_array){
            return $menu_ids;
        }
        return implode($menu_ids,',');
    }
}
            