<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRolePermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Services\BaseService;

class AdminRolesService extends BaseService
{

    /**
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    public function getRoleList($page,$pageNum)
    {
        if (!$roles_list = OaAdminRolesRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
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
            $perm = OaAdminRolePermissionsRepository::getList(['role_id' => $value['id']]);
            $perm_ids = array_column($perm,'permission_id');
            $value['permission_name'] = [];
            $value['permission_slug'] = [];
            if (!empty($perm_ids)){
                $perm_list = OaAdminPermissionsRepository::getList(['id' => ['in', $perm_ids]]);
                $value['permission_name'] = array_column($perm_list,'name');
                $value['permission_slug'] = array_column($perm_list,'slug');
            }
        }
        $this->setMessage('获取成功！');
        return $roles_list;
    }
}
            