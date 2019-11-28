<?php
namespace App\Services\Oa;


use App\Enums\AdminMenuEnum;
use App\Repositories\OaAdminMenuRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRoleMenuRepository;
use App\Repositories\OaAdminRolePermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminMenuService extends BaseService
{
    use HelpTrait;
    #错误信息
    public $error;


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
        $parent_id = $request['parent_id'] ?? 0;
        if ($parent_id != 0){
            $parent_level = OaAdminMenuRepository::getField(['id' => $parent_id],'level');
            if (empty($parent_level)){
                $this->setError('父级菜单不存在！');
                return false;
            }
            $menu_level = $parent_level + 1;
        }
        if (OaAdminMenuRepository::exists(['primary_key' => $request['primary_key']])){
            $this->setError('菜单主键已被使用！');
            return false;
        }
        if (OaAdminPermissionsRepository::exists(['slug' => $request['permission']])){
            $this->setError('权限标识已被使用！');
            return false;
        }
        DB::beginTransaction();
        $permission = ['name' => $request['title'],'slug' => $request['permission'],'http_method' => $request['method'] ?? '','http_path' => $request['path'] ?? ''];
        if (!OaAdminPermissionsRepository::createPermission($permission)){
            $this->setError('权限创建失败！');
            DB::rollBack();
            return false;
        }

        if (OaAdminMenuRepository::exists(['title' => $request['title'],'parent_id' => $parent_id])){
            $this->setError('标题已被使用！');
            DB::rollBack();
            return false;
        }
        $menu_data = [
            'primary_key'=> $request['primary_key'],
            'type'      => $request['type'],
            'parent_id' => $parent_id,
            'path'      => $request['path'] ?? '',
            'vue_route' => $request['vue_route'] ?? '',
            'level'     => $menu_level,
            'title'     => $request['title'],
            'icon'      => $request['icon'],
            'method'    => $request['method'] ?? '',
            'permission'=> $request['permission'],
        ];
        if (!$menu_id = OaAdminMenuRepository::createMenu($menu_data)){
            DB::rollBack();
            $this->setError('菜单添加失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功！');
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
        if (!empty($user->permission_ids)){
            $permissions_ids = explode(',', $user->permission_ids);
            if (!empty($permissions = OaAdminPermissionsRepository::getList(['id' => ['in', $permissions_ids]],['slug']))){
                $permissions_slugs = array_column($permissions,'slug');
                $menu_list = OaAdminMenuRepository::getMenuList(['permission' => ['in', $permissions_slugs]]);
            }
        }
        if (empty($user->permission_ids) && empty($user->role_ids)){
            $this->setMessage('暂无列表！');
            return [];//没有可以展示的列表
        }
        #检验是否有超级权限
        if ($role_perm = OaAdminRolePermissionsRepository::getList(['role_id' => explode(',',trim($user->role_ids,','))],['permission_id'])){
            $perm_ids  = array_column($role_perm,'permission_id');
            $perm_infos= OaAdminPermissionsRepository::getList(['id' => ['in', $perm_ids],'slug' => '*']);
            if (!empty($perm_infos)){
                #此处有所有权限，直接返回所有菜单
                $this->setMessage('获取成功！');
                return OaAdminMenuRepository::getMenuList(['id' => ['>',0]]);
            }
        }

        $menu_info   = OaAdminRoleMenuRepository::getList(['role_id' => explode(',',trim($user->role_ids,','))],['menu_id']);
        $menu_ids = array_column($menu_info,'menu_id');
        $menu_list  += OaAdminMenuRepository::getMenuList(['id' => ['in' , $menu_ids]]);
        if (empty($menu_list)){
            $this->setMessage('暂无列表！');
            return [];//没有可以展示的列表
        }
        $parent_ids = array_column($menu_list,'parent_id');
        foreach ($menu_list as $list){
            if (!in_array($list['parent_id'],$parent_ids)){
                $menu_list += OaAdminMenuRepository::getMenuList(['id' => $list['parent_id']]);
            }
        }
        $this->setMessage('获取成功！');
        return $menu_list;
    }

    /**
     * 获取菜单联动列表
     * @param $type
     * @return mixed
     */
    public function linkageList($type)
    {
        $where['type'] = $type;
        $res = [];
        switch ($type){
            case AdminMenuEnum::DIRECTORY:
                $where['type'] = AdminMenuEnum::DIRECTORY;
                $res[] = ['id'    => 0, 'title' => '顶级目录', 'icon'  => ''];
                break;
            case AdminMenuEnum::MENU:
                $where['type'] = AdminMenuEnum::DIRECTORY;
                $res[] = ['id'    => 0, 'title' => '顶级目录', 'icon'  => ''];
                break;
            case AdminMenuEnum::OPERATE:
                $where['type'] = AdminMenuEnum::MENU;
                $res[] = ['id'    => 0, 'title' => '顶级菜单', 'icon'  => ''];
                break;
        }
        if (!$list = OaAdminMenuRepository::getList($where,['id','title','icon','parent_id'])){
            $this->setMessage('暂无数据！');
            return [];
        }
        $parent_ids = array_column($list,'parent_id');
        $parent_list = OaAdminMenuRepository::getList(['id' => ['in',$parent_ids]],['id','title']);
        foreach ($list as &$value){
            if ($value['parent_id'] == 0){
                $value['title'] = reset($res)['title'] . ' - ' . $value['title'];
            }
            if ($parent = $this->searchArray($parent_list,'id',$value['parent_id'])){
                $value['title'] = reset($parent)['title'] . ' - ' . $value['title'];
            }
            unset($value['parent_id']);
        }
        $this->setMessage('获取成功！');
        return array_merge($res,$list);
    }

    /**
     * 修改菜单
     * @param $request
     * @return bool
     */
    public function editMenu($request)
    {
        if (!$menu = OaAdminMenuRepository::getOne(['id' => $request['id']])){
            $this->setError('菜单不存在！');
            return false;
        }
        $menu_level = 1;
        $parent_id = $request['parent_id'] ?? 0;
        if ($parent_id != 0){
            $parent_level = OaAdminMenuRepository::getField(['id' => $parent_id],'level');
            if (empty($parent_level)){
                $this->setError('父级菜单不存在！');
                return false;
            }
            $menu_level = $parent_level + 1;
        }
        if (OaAdminMenuRepository::exists(['primary_key' => $request['primary_key'],'id' => ['<>',$request['id']]])){
            $this->setError('菜单主键已被使用！');
            return false;
        }
        $permission = ['http_method' => $request['method'] ?? '','http_path' => $request['path'] ?? '','updated_at' => date('Y-m-d H:i:s')];
        if (!OaAdminPermissionsRepository::getUpdId(['slug' => $menu['permission']],$permission)){
            $this->setError('权限创建失败！');
            DB::rollBack();
            return false;
        }
        if (OaAdminMenuRepository::exists(['title' => $request['title'],'parent_id' => $parent_id,'id' => ['<>',$request['id']]])){
            $this->setError('标题已被使用！');
            return false;
        }
        $menu_data = [
            'primary_key'=> $request['primary_key'],
            'type'      => $request['type'],
            'parent_id' => $parent_id,
            'path'      => $request['path'] ?? '',
            'vue_route' => $request['vue_route'] ?? '',
            'level'     => $menu_level,
            'title'     => $request['title'],
            'icon'      => $request['icon'],
            'method'    => $request['method'] ?? '',
            'updated_at'=> date('Y-m-d H:i:s')
        ];
        DB::beginTransaction();
        if (!$menu_id = OaAdminMenuRepository::getUpdId(['id' => $request['id']],$menu_data)){
            DB::rollBack();
            $this->setError('菜单修改失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取菜单详情
     * @param $id
     * @return bool|null
     */
    public function menuDetail($id)
    {
        $column = ['id','primary_key','type','parent_id','title','icon','path','vue_route','method','permission'];
        if (!$menu = OaAdminMenuRepository::getOne(['id' => $id],$column)){
            $this->setError('菜单不存在！');
            return false;
        }
        $this->setMessage('获取成功！');
        return $menu;
    }

    /**
     * 获取所有菜单
     * @return mixed
     */
    public function getAllMenu()
    {
        if (!$list = OaAdminMenuRepository::getAll(['id','primary_key','type','path','vue_route','title','method','url'])){
            $this->setError('获取失败！');
            return false;
        }
        return $list;
    }
}
