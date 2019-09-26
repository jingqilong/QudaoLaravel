<?php
namespace App\Services\Oa;


use App\Enums\AdminMenuEnum;
use App\Repositories\OaAdminMenuRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRoleMenuRepository;
use App\Repositories\OaAdminRolePermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminMenuService extends BaseService
{
    #错误信息
    public $error;

    #http访问方法
    protected $http_methods = ['POST', 'GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];

    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }

    /**
     * 添加菜单
     * @param $request
     * @return bool
     */
    public function addMenu($request)
    {
        $menu_level = 1;
        if ($request['parent_menu'] != 0){
            $parent_level = OaAdminMenuRepository::getField(['id' => $request['parent_menu'], 'type' => AdminMenuEnum::MENU],'level');
            if (empty($parent_level)){
                $this->setError('父级菜单不存在！');
                return false;
            }
            $menu_level = $parent_level + 1;
        }
        $role_ids = $request['role_id'] ?? '';
        $roles      = explode(',',$role_ids);
        if (!empty($roles)){
            foreach ($roles as $id){
                if (!OaAdminRolesRepository::exists(['id' => $id])){
                    $this->setError('角色'.$id.'不存在！');
                    return false;
                }
            }
        }
        if (isset($request['permission']) || !empty($request['permission'])){
            if (!OaAdminPermissionsRepository::exists(['slug' => $request['permission']])){
                $this->setError('权限不存在！');
                return false;
            }
        }
        $menu_data = [
            'parent_id' => $request['parent_menu'],
            'path'      => $request['path'] ?? '',
            'level'     => $menu_level,
            'title'     => $request['title'],
            'icon'      => $request['icon'],
            'permission'=> $request['permission'] ?? '',
        ];
        DB::beginTransaction();
        if (!$menu_id = OaAdminMenuRepository::createMenu($menu_data)){
            DB::rollBack();
            $this->setError('菜单添加失败！');
            return false;
        }
        if (!empty($roles)){
            $role_menu = [];
            foreach ($roles as $id){
                $role_menu[$id]['menu_id'] = $menu_id;
                $role_menu[$id]['role_id'] = $id;
                $role_menu[$id]['created_at'] = date('Y-m-d H:m:s');
                $role_menu[$id]['updated_at'] = date('Y-m-d H:m:s');
            }
            if (!OaAdminRoleMenuRepository::create($role_menu)){
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
     * 添加权限
     * @param $request
     * @return bool
     */
    public function addPermission($request)
    {
        if (OaAdminPermissionsRepository::exists(['slug' => $request['slug']])){
            $this->setError('标识符已被占用');
            return false;
        }
        $http_method = $request['http_method'] ?? '';
        if (!empty($http_method)){
            $methods = explode(',',$http_method);
            foreach ($methods as $method){
                if (!in_array($method,$this->http_methods)){
                    $this->setError('暂不支持'.$method.' HTTP方法');
                    return false;
                }
            }
        }
        $add_data = [
            'name'          => $request['name'],
            'slug'          => $request['slug'],
            'http_method'   => $http_method,
            'http_path'     => $request['http_path'] ?? '',
        ];
        if (!$roles_id = OaAdminPermissionsRepository::createPermission($add_data)){
            $this->setError('权限添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function addRoles($request)
    {
        if (OaAdminRolesRepository::exists(['slug' => $request['slug']])){
            $this->error = '标识符已被占用';
            return false;
        }
        $permission_ids = $request['permission_ids'] ?? '';
        $permission_arr = [];
        if (!empty($permission_ids)){
            $ids_str = trim($permission_ids,',');
            $permissions = explode(',',$ids_str);
            if (!$permissions_all = OaAdminPermissionsRepository::getList(['id' => ['in', $permissions]])){
                $this->error = '无效的权限信息';
                return false;
            }
            if (count($permissions_all) != count($permissions)){
                $this->error = '存在无效的权限信息';
                return false;
            }
            $permission_arr = $permissions;
        }
        $add_roles = [
            'name'          => $request['name'],
            'slug'          => $request['slug'],
        ];
        DB::beginTransaction();
        if (!$roles_id = OaAdminRolesRepository::createRoles($add_roles)){
            DB::rollBack();
            $this->error = '角色添加失败！';
            return false;
        }
        if (!empty($permission_arr))
            if (!OaAdminRolePermissionsRepository::createRelate(['role_id' => $roles_id, 'permission_ids' => $permission_arr])){
                DB::rollBack();
                $this->error = '角色权限添加失败！';
                return false;
            }
        DB::commit();
        return true;
    }

    /**
     * 获取菜单列表
     * @return array|bool|null
     */
    public function getMenuList()
    {
        $user = $this->auth->user();
        $menu_list = [];
        $column = ['*'];
        if (!empty($user->permissions)){
            $permissions_ids = explode(',', $user->permissions);
            if (!empty($permissions = OaAdminPermissionsRepository::getList(['id' => ['in', $permissions_ids]],['slug']))){
                $permissions_slugs = array_column($permissions,'slug');
                $menu_list = OaAdminMenuRepository::getMenuList(['permission' => ['in', $permissions_slugs],'type' => AdminMenuEnum::MENU],$column);
            }
        }
        if (empty($user->permissions) && empty($user->role_id)){
            $this->setMessage('暂无列表！');
            return [];//没有可以展示的列表
        }
        if ($role_perm = OaAdminRolePermissionsRepository::getList(['role_id' => $user->role_id],['permission_id'])){
            $perm_ids  = array_column($role_perm,'permission_id');
            $perm_infos= OaAdminPermissionsRepository::getList(['id' => ['in', $perm_ids],'slug' => '*']);
            if (!empty($perm_infos)){
                #此处有所有权限，直接返回所有菜单
                $this->setMessage('获取成功！');
                return OaAdminMenuRepository::getMenuList(['type' => AdminMenuEnum::MENU],$column);
            }
        }

        $menu_info   = OaAdminRoleMenuRepository::getList(['role_id' => $user->role_id],['menu_id']);
        $menu_ids = array_column($menu_info,'menu_id');
        $menu_list  += OaAdminMenuRepository::getMenuList(['id' => ['in' , $menu_ids]],$column);
        if (empty($menu_list)){
            $this->setMessage('暂无列表！');
            return [];//没有可以展示的列表
        }
        $parent_ids = array_column($menu_list,'parent_id');
        foreach ($menu_list as $list){
            if (!in_array($list['parent_id'],$parent_ids)){
                $menu_list += OaAdminMenuRepository::getMenuList(['id' => $list['parent_id']],$column);
            }
        }
        $this->setMessage('获取成功！');
        return $menu_list;
    }
}
