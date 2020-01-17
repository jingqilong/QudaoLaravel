<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminMenuRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRoleMenuRepository;
use App\Repositories\OaAdminRolePermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class AdminRolesService extends BaseService
{

    /**
     * 获取角色列表
     * @return mixed
     */
    public function getRoleList()
    {
        if (!$roles_list = OaAdminRolesRepository::getList(['id' => ['>',0]],['*'],'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        unset($roles_list['first_page_url'], $roles_list['from'],
            $roles_list['from'], $roles_list['last_page_url'],
            $roles_list['next_page_url'], $roles_list['path'],
            $roles_list['prev_page_url'], $roles_list['to']);
        if (empty($roles_list['data'])){
            $this->setMessage('暂无数据!');
            return $roles_list;
        }
        foreach ($roles_list['data'] as &$value){
            $perm = OaAdminRolePermissionsRepository::getAllList(['role_id' => $value['id']]);
            $perm_ids = array_column($perm,'permission_id');
            $value['permission_name'] = [];
            $value['permission_slug'] = [];
            if (!empty($perm_ids)){
                $perm_list = OaAdminPermissionsRepository::getAllList(['id' => ['in', $perm_ids]]);
                $value['permission_name'] = array_column($perm_list,'name');
                $value['permission_slug'] = array_column($perm_list,'slug');
            }
        }
        $this->setMessage('获取成功！');
        return $roles_list;
    }

    /**
     * 添加角色
     * @param $request
     * @return bool
     */
    public function addRoles($request)
    {
        if (OaAdminRolesRepository::exists(['slug' => $request['slug']])){
            $this->setError('标识符已被占用!');
            return false;
        }
        $permission_ids     = $request['permission_ids'] ?? '';
        $permission_ids     = empty($permission_ids) ? '' : explode(',',trim($permission_ids,','));
        if (!empty($permission_ids)){
            foreach ($permission_ids as $id){
                if (!OaAdminPermissionsRepository::exists(['id' => $id])){
                    $this->setError('权限'.$id.'不存在！');
                    return false;
                }
            }
        }
        $menu_ids   = $request['menu_ids'] ?? '';
        $menu_ids   = empty($menu_ids) ? '' : explode(',',trim($menu_ids,','));
        if (!empty($menu_ids)){
            foreach ($menu_ids as $id){
                if (!OaAdminMenuRepository::exists(['id' => $id])){
                    $this->setError('菜单'.$id.'不存在！');
                    return false;
                }
            }
        }
        DB::beginTransaction();
        if (!$roles_id = OaAdminRolesRepository::createRoles(['name' => $request['name'],'slug' => $request['slug']])){
            DB::rollBack();
            $this->setError('角色添加失败！');
            return false;
        }
        if (!empty($permission_ids)){
            if (!OaAdminRolePermissionsRepository::createRelate(['role_id' => $roles_id, 'permission_ids' => $permission_ids])){
                DB::rollBack();
                $this->setError('角色权限添加失败！');
                return false;
            }
        }
        if (!empty($menu_ids)){
            if (!OaAdminRoleMenuRepository::createRelate(['role_id' => $roles_id, 'menu_ids' => $menu_ids])){
                DB::rollBack();
                $this->setError('菜单添加失败！');
                return false;
            }
        }
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除角色
     * @param $role_id
     * @return bool
     */
    public function deleteRoles($role_id)
    {
        if (!OaAdminRolesRepository::exists(['id' => $role_id])){
            $this->setError('角色不存在！');
            return false;
        }
        if (OaEmployeeRepository::exists(['role_ids' => ['like','%,'.$role_id.',%']])){
            $this->setError('当前角色已被使用，无法删除！');
            return false;
        }
        if ($role_id == 1){
            $this->setError('此角色无法删除！');
            return false;
        }
        DB::beginTransaction();
        if (!OaAdminRolesRepository::delete(['id' => $role_id])){
            DB::rollBack();
            $this->setError('删除失败！');
            return false;
        }
        #删除角色菜单关联信息
        if (OaAdminRoleMenuRepository::exists(['role_id' => $role_id])){
            if (!OaAdminRoleMenuRepository::delete(['role_id' => $role_id])){
                DB::rollBack();
                $this->setError('删除失败！');
                return false;
            }
        }
        #删除角色权限关联信息
        if (OaAdminRolePermissionsRepository::exists(['role_id' => $role_id])){
            if (!OaAdminRolePermissionsRepository::delete(['role_id' => $role_id])){
                DB::rollBack();
                $this->setError('删除失败！');
                return false;
            }
        }
        $this->setMessage('删除成功!');
        DB::commit();
        return true;
    }

    /**
     * 编辑角色
     * @param $request
     * @return bool
     */
    public function editRoles($request)
    {
        if (!OaAdminRolesRepository::exists(['id' => $request['id']])){
            $this->setError('角色不存在！');
            return false;
        }
        if (OaAdminRolesRepository::exists(['slug' => $request['slug'],'id' => ['<>',$request['id']]])){
            $this->error = '标识符已被占用';
            return false;
        }
        $permission_ids     = $request['permission_ids'] ?? '';
        $permission_ids     = empty($permission_ids) ? '' : explode(',',trim($permission_ids,','));
        if (!empty($permission_ids)){
            foreach ($permission_ids as $id){
                if (!OaAdminPermissionsRepository::exists(['id' => $id])){
                    $this->setError('权限'.$id.'不存在！');
                    return false;
                }
            }
        }
        $menu_ids   = $request['menu_ids'] ?? '';
        $menu_ids   = empty($menu_ids) ? '' : explode(',',trim($menu_ids,','));
        if (!empty($menu_ids)){
            foreach ($menu_ids as $id){
                if (!OaAdminMenuRepository::exists(['id' => $id])){
                    $this->setError('菜单'.$id.'不存在！');
                    return false;
                }
            }
        }
        DB::beginTransaction();
        if (!$roles_id = OaAdminRolesRepository::updateRoles($request['id'],['name' => $request['name'],'slug' => $request['slug']])){
            DB::rollBack();
            $this->setError('修改失败！');
            return false;
        }
        if (!OaAdminRolePermissionsRepository::createRelate(['role_id' => $roles_id, 'permission_ids' => $permission_ids])){
            DB::rollBack();
            $this->setError('角色权限添加失败！');
            return false;
        }
        if (!OaAdminRoleMenuRepository::createRelate(['role_id' => $roles_id, 'menu_ids' => $menu_ids])){
            DB::rollBack();
            $this->setError('菜单添加失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取角色详情
     * @param $role_id
     * @return bool|null
     */
    public function getRoleDetails($role_id)
    {
        $column = ['id','name','slug','created_at','updated_at'];
        if (!$role = OaAdminRolesRepository::getOne(['id' => $role_id],$column)){
            $this->setError('角色不存在！');
            return false;
        }
        $role['menu_ids'] = OaAdminRoleMenuRepository::getMenuIds($role_id);
        $role['permission_ids'] = OaAdminRolePermissionsRepository::getPermissionIds($role_id);
        $this->setMessage('获取成功！');
        return $role;
    }
}
            