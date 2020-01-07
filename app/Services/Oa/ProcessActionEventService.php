<?php

namespace App\Services\Oa;

use App\Enums\ProcessActionEventTypeEnum;
use App\Enums\ProcessCommonStatusEnum;
use App\Enums\ProcessEventEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaProcessActionEventRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Tolawho\Loggy\Facades\Loggy;

/**
 * @desc 动作事件操作  ModifiedBy: bardo
 * Class ProcessActionEventService
 * @package App\Services\Oa
 */
class ProcessActionEventService extends BaseService
{
    use HelpTrait;
    /**
     * 添加动作事件
     * @param $request
     * @return bool
     */
    public function addActionEvent($request)
    {
        $node_action_result_id = $request['node_action_result_id'] ?? 0;
        if (ProcessActionEventTypeEnum::ACTION_RESULT_EVENT == $request['event_type'] && empty($node_action_result_id)){
            $this->setError('节点动作结果ID不能为空！');
            return false;
        }
        if (!OaProcessNodeRepository::exists(['id' => $request['node_id']])){
            $this->setError('该节点不存在！');
            return false;
        }
        if (!empty($node_action_result_id)){
            if (!OaProcessNodeActionsResultRepository::exists(['id' => $node_action_result_id])){
                $this->setError('节点动作结果不存在！');
                return false;
            }
        }
        $check_event = OaProcessEventsRepository::isEnabled($request['event_id']);
        if (100 == $check_event['code']){
            $this->setError($check_event['message']);
            return false;
        }
        $add_event_arr = [
            'node_id'               => $request['node_id'],
            'node_action_result_id' => $node_action_result_id,
            'event_type'            => $request['event_type'],
            'event_id'              => $request['event_id'],
            'principals_type'       => $request['principals_type'],
        ];
        if (OaProcessActionEventRepository::exists($add_event_arr)){
            $this->setError('该事件已添加，请勿重复添加！');
            return false;
        }
        $add_event_arr['created_at'] = $add_event_arr['updated_at'] = time();
        if (!OaProcessActionEventRepository::getAddId($add_event_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
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
        if (!OaProcessActionEventRepository::exists(['id' => $request['node_action_event_id']])){
            $this->setError('该节点动作事件不存在！');
            return false;
        }
        $check_event = OaProcessEventsRepository::isEnabled($request['event_id']);
        if (100 == $check_event['code']){
            $this->setError($check_event['message']);
            return false;
        }
        $update = [
            'event_id'          => $request['event_id'],
            'principals_type'   => $request['principals_type'],
            'updated_at'        => time()
        ];
        if (!OaProcessActionEventRepository::getUpdId(['id' => $request['node_action_event_id']],$update)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * @desc 获取动作事件列表
     * @param $request
     * @return bool|null
     */
    public function getActionEventList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $node_action_result_id = $request['node_action_result_id'] ?? 0;
        $where      = [
            'node_id'                   => $request['node_id'],
            'node_action_result_id'     => 0,
            'event_type'                => ProcessActionEventTypeEnum::NODE_EVENT
        ];
        if (!empty($node_action_result_id)){
            $where['node_action_result_id'] = $node_action_result_id;
            $where['event_type']            = ProcessActionEventTypeEnum::ACTION_RESULT_EVENT;
        }
        $column = ['*'];
        if (!$process_event_list = OaProcessActionEventRepository::getList($where,$column,'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $process_event_list = $this->removePagingField($process_event_list);
        if (empty($process_event_list['data'])){
            $this->setMessage('暂无数据！');
            return $process_event_list;
        }
        $event_ids  = array_column($process_event_list['data'],'event_id');
        $event_list = OaProcessEventsRepository::getList(['id' => ['in',$event_ids]],['id','name','event_type','status']);
        foreach ($process_event_list['data'] as &$value){
            $value['principals_type_title'] = ProcessPrincipalsEnum::getPprincipalLabel($value['principals_type']);
            $value['event_name']            = '';
            $value['base_event_type']       = 0;
            $value['base_event_type_title'] = '';
            $value['status']                = 0;
            $value['status_title']          = '';
            foreach ($event_list as $event){
                if ($event['id'] == $value['event_id']){
                    $value['event_name']            = $event['name'];
                    $value['base_event_type']       = $event['event_type'];
                    $value['base_event_type_title'] = ProcessEventEnum::getLabelByValue($event['event_type']);
                    $value['status']                = $event['status'];
                    $value['status_title']          = ProcessCommonStatusEnum::getLabelByValue($event['status']);
                    break;
                }
            }
            $value['created_at'] = empty($value['created_at']) ? '' : date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = empty($value['updated_at']) ? '' : date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $process_event_list;
    }

    /**
     * 获取事件列表，可以是节点事件，也可以是节点动作结果事件
     * @param $node_id  //节点事件时传入，否则为0
     * @param $node_action_result_id  //节点动作结果事件时传入，否则为0
     * @param int $event_type   事件类型 0：节点事件 1：节点动作结果事件
     * @return mixed
     */
    public function getActionEventListWithType($node_id,$node_action_result_id, $event_type=0){
        if((0==$event_type)&& (empty($node_id))){
            $message="查询节点事件时节点ID不可以为空!";
            $this->setError($message);
            Loggy::write("error",$message);
            return false;
        }
        if((1==$event_type)&& (empty($node_action_result_id))){
            $message="查询节点动作结果事件时节点动作结果ID不可以为空!";
            $this->setError($message);
            Loggy::write("error",$message);
            return false;
        }
        $where=[];
        if(!empty($node_id)){
            $where['node_id']=$node_id;
        }
        if(!empty($node_action_result_id)){
            $where['node_action_result_id']=$node_action_result_id;
        }
        if(0==$event_type){
            unset($where['node_action_result_id']);
        }
        $where['event_type']=$event_type;
        $page = 1;
        $pageNum= 100;
        $event_list = OaProcessActionEventRepository::getList($where,['*'],'id','asc',$page,$pageNum);
        $event_ids  = array_column($event_list['data'],'event_id');
        if ($event_ids){
            $event_defined_list = OaProcessEventsRepository::getAssignList($event_ids);
            foreach ($event_list['data'] as &$value){
                foreach ($event_defined_list as $event){
                    if ($value['event_id'] == $event['id']){
                        $value['event_defined_type'] = $event['event_type'];break;
                    }
                }
            }
        }
        return $event_list['data'];
    }

}