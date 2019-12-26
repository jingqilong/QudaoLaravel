<?php
namespace App\Traits;

use App\Enums\ProcessEventEnum;
use App\Enums\ProcessActionEventTypeEnum;
use App\Enums\ProcessEventMessageTypeEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Services\Oa\ProcessActionEventService;
use App\Services\Oa\ProcessActionPrincipalsService;
use App\Services\Oa\ProcessActionResultsService;
use App\Services\Oa\ProcessNodeService;
use App\Services\Oa\ProcessRecordService;
use App\Services\Oa\ProcessTransitionService;
use Illuminate\Support\Facades\Config;
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
        $start_node = OaProcessNodeRepository::getOne([['process_id' => $process_id],['position' => Config::get('process.start_node')]]);
        if(!$start_node){
            $message = "获取开始节点失败";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        $record_data = [
            'business_id'       	=> $business_id,
            'process_id'        	=> $process_id,
            'process_category'  	=> $process_category,
            'node_id'           	=> $start_node['id'],
            'position'           	=> 1,
            'node_action_result_id' => 0,
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
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(
            $start_node['id'],0,ProcessActionEventTypeEnum::NODE_EVENT);
        //触发流程事件
        $this->triggerNodeEvent($event_list,$record_data);
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
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(
            0,$process_record_data['node_action_result_id'],ProcessActionEventTypeEnum::ACTION_RESULT_EVENT);
        //触发流程事件
        $this->triggerResultEvent($event_list,$process_record_data);
        $where['process_id'] = $process_record_data['process_id'];
        $where['node_action_result_id'] = $process_record_data['node_action_result_id'];
        //获取结果的流转
        if(!$transition = app(ProcessTransitionService::class)->getTransitionByResult($where)){
            Loggy::write('error',"获取流转失败！",$where);
            return ['code'=>200,  'message' => "流程发起成功！" ];
        }
        if(1< $transition['status']){ //节点已结束或终止
            return ['code'=>200,  'message' => "流程发起成功！" ];
        }
        $next_node_id = $transition['next_node'];
        $next_event_data = $this->addNextProcessRecord($process_record_data,$next_node_id);
        if(!$next_event_data){
            Loggy::write('error',"添加审核任务新节点失败！".$next_node_id);
        }
        $next_event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(
            $next_node_id,0,ProcessActionEventTypeEnum::NODE_EVENT);
        if($next_event_list){
            $this->triggerNodeEvent($next_event_list,$next_event_data);
        }
        return ['code'=>200,  'message' => "流程发起成功！" ];
    }

    /**
     * 用于审核时获取$node_action_result_list
     * @param $node_action_id
     * @return array
     */
    public function getNodeActionResult($node_action_id){
        if(!$node_action_result_res = OaProcessNodeActionsResultRepository::getList(['node_action_id'=>$node_action_id])){
            return ['code'=>100,  'message' => "获取失败！" ];
        }
        $node_action_result_list = [];
        foreach($node_action_result_res as $value){
            $newNode = &$node_action_result_list[];
            $newNode['node_action_result_id'] = $value['id'];
            $newNode['action_result_id'] = $value['action_result_id'];
            $newNode['action_result_label'] =  app(ProcessActionResultsService::class)->getActionResultText( $value['action_result_id']) ;
        }
        return ['code'=>200,  'data' => $node_action_result_list ];
    }

    /**
     * @desc 获取当前业务的审核列表
     * @param $request
     * @return mixed
     */
    public function getProcessRecordList($request){
        $processRecordService = new ProcessRecordService();
        $recode_list = $processRecordService->getProcessRecodeList($request);
        if ($recode_list == false){
            return ['code' => 100,$processRecordService->error];
        }
        return ['code' => 200,'message' => $processRecordService->message,'data' => $recode_list];
    }


    /**
     * @desc  添加下一个待审记录节点。
     * @param $current_data
     * @param $transition
     * @return mixed
     */
    public function addNextProcessRecord($current_data,$transition){
        if(!isset($current_data['process_id'])){
            $message = "未收到流程ID!";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        $node = OaProcessNodeRepository::getOne(['process_id' => $current_data['process_id']]);
        if(!$node){
            $message = "获取下一节点失败";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        $record_data = [
            'business_id'       	=> $current_data['business_id'],
            'process_id'        	=> $current_data['process_id'],
            'process_category'  	=> $current_data['process_category'],
            'node_id'           	=> $transition['next_node'],
            'position'           	=> $node['position'],
            'node_action_result_id' => 0,
            'operator_id'       	=> 0,
            'note'              	=> '',
        ];
        $processRecordService = new ProcessRecordService();
        $result = $processRecordService->addRecord($record_data);
        if(!$result){
            $message = $processRecordService->error;
            return ['code'=>100,  'message' => $message ];
        }
        return $record_data;
    }


    /**
     * @desc 流程触发事件总接口函数
     * @param $event_list
     * @param $event_params
     * @return bool
     */
    public function triggerResultEvent($event_list,$event_params){
        //数据有效性检测
        if(!isset($event_params['business_id'],$event_params['node_id'],$event_params['process_category'])){
            Loggy::write('error',"节点事件需要变量business_id、node_id、process_category不全！ ",$event_params );
            return false;
        }
        //获取所有的参与人  返回具有以下KEY的列表 {receiver_iden:,receiver_name:,receiver_id}
        $stakeholders = app(ProcessActionPrincipalsService::class)->getResultEventPrincipals(
            $event_params['node_action_result_id'],$event_params['business_id'],$event_params['process_category']);
        if(empty($stakeholders)){
            Loggy::write('error',"没有事件参与人,node_id::" .$event_params['node_id'] );
            return false;
        }
        //定义事件消息类型 //TODO 这里是不是要升级为配置，或数据表
        $message_type = [
            ProcessPrincipalsEnum::EXECUTOR => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
            ProcessPrincipalsEnum::AGENT => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
            ProcessPrincipalsEnum::STARTER => ProcessEventMessageTypeEnum::STATUS_NOTICE,
            ProcessPrincipalsEnum::SUPERVISOR => ProcessEventMessageTypeEnum::STATUS_NOTICE
        ];
        //初始化事件的数据。
        $send_data = [
            'business_id'       	=> $event_params['business_id'],
            'process_id'        	=> $event_params['process_id'],
            'process_category'  	=> $event_params['process_category'],
            'node_id'           	=> $event_params['node_id'],
        ];
        //通过 process_id 获取流程名称。NODE 获取动作名称。
        $send_data['process_full_name'] = app(ProcessNodeService::class)->getProcessNodeFullName(
            $event_params['business_id'],$event_params['node_id']
        );
        foreach($event_list as $event){
            foreach ($stakeholders as $receiver){
                $event_data = $send_data;
                $event_data['receiver'] = $receiver;
                $event_data['event_type'] =  $event['event_type'];
                //所以，到这里，还是要具体获取这些人的ID
                if(ProcessEventEnum::DINGTALK_EMAIL ==  $event['event_type']){
                    event(new SendDingTalkEmail($event_data));
                }
                if(ProcessEventEnum::SMS ==  $event['event_type']){
                    event(new SendSiteMessage($event_data));
                }
                if(ProcessEventEnum::SITE_MESSAGE ==  $event['event_type']){
                    event(new SendFlowSms($event_data));
                }
//                if(ProcessEventEnum::WECHAT_PUSH ==  $event['event_type']){
//                    //后续加上
//                    ;
//                }
            }
        }
        return true;
    }

    /**
     * @desc 流程触发事件总接口函数
     * @param $event_list
     * @param $event_params
     * @return bool
     */
    public function triggerNodeEvent($event_list,$event_params){
        //数据有效性检测
        if(!isset($event_params['business_id'],$event_params['node_id'],$event_params['process_category'])){
            Loggy::write('error',"节点事件需要变量business_id、node_id、process_category不全！ ",$event_params );
            return false;
        }
        //获取所有的参与人  返回具有以下KEY的列表 {receiver_iden:,receiver_name:,receiver_id}
        $stakeholders = app(ProcessActionPrincipalsService::class)->getNodeEventPrincipals(
            $event_params['node_id'],$event_params['business_id'],$event_params['process_category']);
        if(empty($stakeholders)){
            Loggy::write('error',"没有事件参与人,node_id::" .$event_params['node_id'] );
            return false;
        }
        //定义事件消息类型 //TODO 这里是不是要升级为配置，或数据表
        $message_type = [
            ProcessPrincipalsEnum::EXECUTOR => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
            ProcessPrincipalsEnum::AGENT => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
            ProcessPrincipalsEnum::STARTER => ProcessEventMessageTypeEnum::STATUS_NOTICE,
            ProcessPrincipalsEnum::SUPERVISOR => ProcessEventMessageTypeEnum::STATUS_NOTICE
        ];
        //初始化事件的数据。
        $send_data = [
                'business_id'       	=> $event_params['business_id'],
                'process_id'        	=> $event_params['process_id'],
                'process_category'  	=> $event_params['process_category'],
                'node_id'           	=> $event_params['node_id'],
            ];
        //通过 process_id 获取流程名称。NODE 获取动作名称。
        $send_data['process_full_name'] = app(ProcessNodeService::class)->getProcessNodeFullName(
            $event_params['business_id'],$event_params['node_id']
        );
        foreach($event_list as $event){
           foreach ($stakeholders as $receiver){
               $event_data = $send_data;
               $event_data['receiver'] = $receiver;
               $event_data['event_type'] =  $event['event_type'];
               //所以，到这里，还是要具体获取这些人的ID
               if(ProcessEventEnum::DINGTALK_EMAIL ==  $event['event_type']){
                   event(new SendDingTalkEmail($event_data));
               }
               if(ProcessEventEnum::SMS ==  $event['event_type']){
                   event(new SendSiteMessage($event_data));
               }
               if(ProcessEventEnum::SITE_MESSAGE ==  $event['event_type']){
                   event(new SendFlowSms($event_data));
               }
//               if(ProcessEventEnum::WECHAT_PUSH ==  $event['event_type']){
//                   //后续加上
//                   ;
//               }
           }
        }
        return true;
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
     * @param int $page
     * @param int $page_num
     * @return array
     */
    public function getNodeListByUserid($user_id,$page = 1,$page_num = 20){
        $processRecordService = new ProcessRecordService();
        $recode_list = $processRecordService->getNodeListByUserId($user_id,$page,$page_num);
        if ($recode_list == false){
            return ['code' => 100,$processRecordService->error];
        }
        return ['code' => 200,'message' => $processRecordService->message,'data' => $recode_list];
    }


}