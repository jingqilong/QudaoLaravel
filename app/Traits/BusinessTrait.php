<?php
namespace App\Traits;

use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionRepository;
use App\Services\Oa\ProcessActionEventService;
use App\Services\Oa\ProcessRecordService;
use Tolawho\Loggy\Facades\Loggy;
use App\Events\SendDingTalkEmail;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;

/**
 * Class BusinessTrait
 * @package App\Traits
 * @desc 这是给业务程序来使用的trait
 */
trait BusinessTrait
{
    /**
     * @desc 发起流程请求
     * @param $business_id    ,业务ID
     * @param $process_category   ,流程分类
     * @return mixed
     */
    public function addNewProcessRecord($business_id,$process_category){
        //获取流程定义ID
        $process_id = OaProcessDefinitionRepository::getOne(['category_id'=>$process_category],['id',]);
        if(!$process_id){
            $message = "此类流程未定义，请联系系统管理员，定义后再处理";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        //获取第一个流程节点ID
        $node_id = OaProcessNodeRepository::getList([['process_id' => $process_id],['$position' => 1]]);
        $record_data = [
            'business_id'       	=> $business_id,
            'process_id'        	=> $process_id,
            'process_category'  	=> $process_category,
            'node_id'           	=> $node_id,
            'position'           	=> 1,
            'action_result_id'  	=> 0,
            'operator_id'       	=> 0,
            'note'              	=> '',
        ];
        //创建流程节点
        $processRecordService = new ProcessRecordService();
        $result = $processRecordService->addRecord($record_data);
        if(!$result){
            $message = $processRecordService->error;
            return ['code'=>100,  'message' => $message ];
        }
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType($node_id,0,0);
        //触发流程事件
        $this->triggerEvent($event_list,$record_data);
        return ['code'=>200,  'message' => "流程发起成功！" ];
    }

    /**
     * @desc 客户端审核提交
     * @param $process_record_id
     * @param $process_record_data
     * @return array
     */
    public function updateProcessRecord($process_record_id, $process_record_data){
        if(!isset($process_record_data['action_result_id'])){
            $message = "缺少节点动作结果id！";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        if(!isset($process_record_data['operator_id'])){
            $message = "缺少操作人id";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        //是否发起后，直接通过，并请求下一节点
        $processRecordService = new ProcessRecordService();
        $result = $processRecordService->updateRecord($process_record_id,$process_record_data);
        if(!$result){
            $message = $processRecordService->error;
            return ['code'=>100,  'message' => $message ];
        }
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(0,$process_record_data['node_action_result_id'],1);
        //触发流程事件
        $this->triggerEvent($event_list,$process_record_data);
        $where['process_id'] = $process_record_data['process_id'];
        $where['action_result_id'] = $process_record_data['action_result_id'];
        //获取是否拥有下一个节点
        $next_node = OaProcessTransitionRepository::getOne($where);
        if($next_node){
            if(0!=$next_node['next_node']){
                $next_nnode_id = $next_node['next_node']
            }
        }
        if($next_nnode_id){  //审核完成后，增加新节点   //TODO 问题：我如何判断当前已经结束？
            $next_event_data  =  $this->addNextProcessRecord($process_record_data,$next_nnode_id);
            if($next_event_data){
                $event_list =  app(ProcessActionEventService::class)->getActionEventList($node_id,0,0);
                $this->triggerEvent($event_list,$next_event_data);
            }
        }
        return ['code'=>200,  'message' => "流程发起成功！" ];
    }

    /**
     * @desc 获取当前业务的审核列表
     * @param $request
     * @return mixed
     */
    public function getProcessRecordList($request){
        $processRecordService = new ProcessRecordService();
        return $processRecordService->getProcessRecodeList($request);
    }


    /**
     * @desc  添加下一个待审记录节点。
     * @param $business_id
     * @param $process_id
     * @param $node_id
     * @param $process_type
     * @param $audit_result
     * @return mixed
     */
    public function addNextProcessRecord($business_id,$process_id, $node_id, $process_type, $audit_result){
        $next_node = $this->hasNextNode($process_id, $node_id, $audit_result);
        if(!$next_node){
            return false;
        }
        //TODO 添加下一个待审记录节点
    }


    /**
     * @desc 流程触发事件总接口函数
     * @param $event_list
     * @param $event_data
     * @return bool
     */
    public function triggerEvent($event_list,$event_data){

        foreach($event_list as $event){
            //事件表有问题，没有用事件类型，同时，也不知发给谁。
            // TODO 这里的问题
            //下面的 receiver只是指定了是监督人， 审核人， 还是发起人， 所以，还要获取到具体的ID。
            $event_data["receiver_type"] = $event['receiver_type'];
            //下面指定了，这类人是来自于哪个终端，即是会员，还是员工，
            $event_data["receiver_from"] = $event['receiver_from'];
            //所以，到这里，还是要具体获取这些人的ID
            if(ProcessEventEnum::DINGTALK_EMAIL ==  $event['execute']){
                event(new SendDingTalkEmail($event_data));
            }
            if(ProcessEventEnum::SMS ==  $event['execute']){
                event(new SendSiteMessage($event_data));
            }
            if(ProcessEventEnum::SITE_MESSAGE ==  $event['execute']){
                event(new SendFlowSms($event_data));
            }
            if(ProcessEventEnum::WECHAT_PUSH ==  $event['execute']){
                //后续加上
                ;
            }
        }
        return false;
    }

    /**
     * @param $process_id
     * @param $node_id
     * @param $audit_result
     * @return mixed
     * @desc 判断这个节点是否有下一具节点,如果有，返回节点详情
     */
    public function hasNextNode($process_id, $node_id, $audit_result){
        $next_node = false;
        //TODO  判断这个节点是否有下一具节点,如果有，返回节点详情

        return $next_node;
    }

    /**
     * @param $business_id
     * @param $process_id
     * @desc 重新启动流程请求
     */
    public function restartProcessRecord($business_id,$process_id){
        //TODO 读取原始数据，提供给用户编辑。
    }

    /**
     * 对于一个任务，重启审核流程。
     * @param $business_data
     * @param $process_id
     * @desc 重新启动流程保存
     */
    public function addRestartNode($business_data,$process_id){
        //TODO 保存业务数据。同时创建一个新的节点，这个薪节点必须要有 old_process_id
    }

    /**
     * @desc DASHBOARD 仪表板中的我的审核列表
     * @param $user_id
     */
    public function getNodeListByUserid($user_id){
        //TODO  获取仪表板中的我的审核列表
    }


}