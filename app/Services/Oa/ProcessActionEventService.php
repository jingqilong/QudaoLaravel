<?php

namespace App\Services\Oa;

use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessActionEventRepository;
use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;
use App\Enums\ProcessPrincipalsEnum;

/**
 * @desc 动作事件操作  ModifiedBy: bardo
 * Class ProcessActionEventService
 * @package App\Services\Oa
 */
class ProcessActionEventService extends BaseService
{
    /**
     * 添加动作事件
     * @param $request
     * @return bool
     */
    public function addActionEvent($request)
    {
        if (OaProcessActionEventRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'node_id'	 => $request['node_id'],
            'node_action_result_id'	 => $request['node_action_result_id'],
            'event_type' => $request['event_type'],
            'event_id' => $request['event_id'],
            'principals_type' => $request['principals_type'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionEventRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除动作事件
     * @param $action_event_id
     * @return bool
     */
    public function deleteActionEvent($action_event_id)
    {
        if (!OaProcessActionEventRepository::exists(['id' => $action_event_id])){
            $this->setError('该事件不存在！');
            return false;
        }
        if (!OaProcessActionEventRepository::delete(['id' => $action_event_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 读取单个$action_event
     * @param $action_event_id
     * @return bool|null
     */
    public function getActionEvent($action_event_id){
        if (!$action_event = OaProcessActionEventRepository::getOne(['id' => $action_event_id])){
            $this->setError('该事件不存在！');
            return false;
        }
        return $action_event;
    }

    /**
     * 修改动作事件
     * @param $request
     * @return bool
     */
    public function editActionEvent($request)
    {
        if (!$action_event = OaProcessActionEventRepository::getOne(['id' => $request['action_event_id']])){
            $this->setError('该事件不存在！');
            return false;
        }
        $upd_arr = [
            'node_id'	 => $request['node_id'],
            'node_action_result_id'	 => $request['node_action_result_id'],
            'event_type' => $request['event_type'],
            'event_id' => $request['event_id'],
            'principals_type' => $request['principals_type'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionEventRepository::getUpdId(['id' => $request['action_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * @desc 获取动作事件列表
     * @param $where
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getActionEventList($where, $page, $pageNum)
    {
        if (empty($where)){
            $where=['id' => ['>',0]];
        }
        if (!$action_list = OaProcessActionEventRepository::getList($where,['*'],'id','asc',$page,$pageNum)){
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
            $value['event_type_label']  = ProcessEventEnum::getLabelByValue($value['event_type']);
            $value['principals_type_label']   = ProcessPrincipalsEnum::getPprincipalLabel($value['principals_type']);
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $action_list;
    }

    /**
     * 获取事件列表，可以是节点事件，也可以是节点动作结果事件
     * @param $node_id  //节点事件时传入，否则为0
     * @param $node_action_id  //节点动作结果事件时传入，否则为0
     * @param int $event_type   事件类型 0：节点事件 1：节点动作结果事件
     * @return mixed
     */
    public function getActionEventListWithType($node_id,$node_action_id, $event_type=0){
        if((0==$event_type)&& (empty($node_id))){
            $message="查询节点事件时节点ID不可以为空!";
            $this->setError($message);
            Loggy::write("error",$message);
            return false;
        }
        if((1==$event_type)&& (empty($node_action_id))){
            $message="查询节点动作结果事件时节点动作结果ID不可以为空!";
            $this->setError($message);
            Loggy::write("error",$message);
            return false;
        }
        $where=[];
        if(!empty($node_id)){
            $where['node_id']=$node_id;
        }
        if(!empty($node_action_id)){
            $where['node_action_id']=$node_action_id;
        }
        if(0==$event_type){
            unset($where['node_action_id']);
        }
        if(0==$event_type){
            unset($where['node_id']);
        }
        $where['event_type']=$event_type;
        $page = 1;
        $pageNum= 100;
        $event_list = OaProcessActionEventRepository::getList($where,['*'],'id','asc',$page,$pageNum);
        return $event_list['data'];
    }

}