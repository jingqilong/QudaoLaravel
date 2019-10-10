<?php
namespace App\Services\Oa;


use App\Enums\ProcessActionEnum;
use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Services\BaseService;

class ProcessActionsService extends BaseService
{

    /**
     * 添加动作
     * @param $request
     * @return bool
     */
    public function addAction($request)
    {
        if (OaProcessActionsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'result'       => $request['result'],
            'status'        => ProcessActionEnum::getConst($request['status']),
            'description'   => $request['description'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除动作
     * @param $action_id
     * @return bool
     */
    public function deleteAction($action_id)
    {
        if (!OaProcessActionsRepository::exists(['id' => $action_id])){
            $this->setError('该动作不存在！');
            return false;
        }
        if (OaProcessNodeActionRepository::exists(['action_id' => $action_id])){
            $this->setError('无法删除正在使用的动作！');
            return false;
        }
        if (!OaProcessActionsRepository::delete(['id' => $action_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改动作
     * @param $request
     * @return bool
     */
    public function editAction($request)
    {
        if (!$action = OaProcessActionsRepository::getOne(['id' => $request['action_id']])){
            $this->setError('该事件不存在！');
            return false;
        }
        if ($action['name'] != $request['name'] && OaProcessActionsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'result'        => $request['result'],
            'status'        => ProcessActionEnum::getConst($request['status']),
            'description'   => $request['description'],
            'updated_at'    => time(),
        ];
        if (OaProcessActionsRepository::getUpdId(['id' => $request['action_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取动作列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getActionList($page, $pageNum)
    {
        if (!$action_list = OaProcessActionsRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($action_list['first_page_url'], $action_list['from'],
            $action_list['from'], $action_list['last_page_url'],
            $action_list['next_page_url'], $action_list['path'],
            $action_list['prev_page_url'], $action_list['to']);
        if (empty($action_list['data'])){
            $this->setMessage('暂无数据!');
            return $action_list;
        }
        foreach ($action_list['data'] as &$value){
            $value['results']       = empty($value['result']) ? [] : explode(',',$value['result']);
            $value['status_label']  = ProcessActionEnum::getStatus($value['status']);
            $value['status']        = ProcessActionEnum::$status[$value['status']];
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $action_list;
    }
}
            