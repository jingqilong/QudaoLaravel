<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminPermissionsRepository;
use App\Services\BaseService;

class AdminPermissionsService extends BaseService
{

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
}
            