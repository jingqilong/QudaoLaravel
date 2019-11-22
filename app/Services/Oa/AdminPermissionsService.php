<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminMenuRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Services\BaseService;

class AdminPermissionsService extends BaseService
{

    #http访问方法
    protected $http_methods = ['POST', 'GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];

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
     * 获取权限列表
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    public function getPermissionList($page,$pageNum)
    {
        if (!$perm_list = OaAdminPermissionsRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($perm_list['first_page_url'], $perm_list['from'],
            $perm_list['from'], $perm_list['last_page_url'],
            $perm_list['next_page_url'], $perm_list['path'],
            $perm_list['prev_page_url'], $perm_list['to']);
        if (empty($perm_list['data'])){
            $this->setMessage('暂无数据!');
            return $perm_list;
        }
        foreach ($perm_list['data'] as &$value){
            $value['http_path'] = explode(',',$value['http_path']);
        }
        $this->setMessage('获取成功！');
        return $perm_list;
    }

    /**
     * 删除权限
     * @param $id
     * @return bool
     */
    public function deletePermission($id)
    {
        if (!$permission = OaAdminPermissionsRepository::getOne(['id' => $id])){
            $this->setError('权限不存在!');
            return false;
        }
        if ($id == 1){
            $this->setError('此权限无法删除!');
            return false;
        }
        if ($menu = OaAdminMenuRepository::getOne(['permission' => $permission])){
            $this->setError('此权限已被菜单《'.$menu['title'].'》使用，无法删除！');
            return false;
        }
        if (!OaAdminPermissionsRepository::delete(['id' =>$id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改权限
     * @param $request
     * @return bool
     */
    public function editPermission($request)
    {
        if (!$permission = OaAdminPermissionsRepository::getOne(['id' => $request['id']])){
            $this->setError('权限不存在!');
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
        $upd_data = [
            'name'          => $request['name'],
            'http_method'   => $http_method,
            'http_path'     => $request['http_path'] ?? '',
        ];
        if (!$roles_id = OaAdminPermissionsRepository::updatePermission($request['id'],$upd_data)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }
}
            