<?php
namespace App\Traits;

use App\Enums\ProcessActionPermissionEnum;
use App\Enums\ProcessEventEnum;
use App\Enums\ProcessActionEventTypeEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeActionsResultViewRepository;
use App\Repositories\OaProcessNodeEventPrincipalsRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordActionsResultViewRepository;
use App\Services\Oa\ProcessActionEventService;
use App\Services\Oa\ProcessActionPrincipalsService;
use App\Services\Oa\ProcessActionResultsService;
use App\Services\Oa\ProcessNodeService;
use App\Services\Oa\ProcessRecordService;
use App\Services\Oa\ProcessTransitionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
        $process_id = OaProcessDefinitionRepository::getField(['category_id'=>$process_category],'id');
        if(!$process_id){
            $message = "此类流程未定义，请联系系统管理员，定义后再处理";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        //获取第一个流程节点ID
        $start_node_position= Config::get('process.start_node_position');
        $start_node         = OaProcessNodeRepository::getOne(['process_id' => $process_id,'position' => $start_node_position]);
        if(!$start_node){
            $message = "获取开始节点失败";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        if (!$operator_list = OaProcessNodeEventPrincipalsRepository::getList(['node_id' => $start_node['id'],'principal_iden' => ProcessPrincipalsEnum::EXECUTOR])){
            $message = '该流程节点没有动作执行人，流程发起失败';
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        DB::beginTransaction();
        foreach ($operator_list as $operator){
            $record_data = [
                'business_id'       	=> $business_id,
                'process_id'        	=> $process_id,
                'process_category'  	=> $process_category,
                'node_id'           	=> $start_node['id'],
                'position'           	=> $start_node_position,
                'operator_id'       	=> $operator['principal_id'],
            ];
            //创建流程节点
            $processRecordService = new ProcessRecordService();
            $result = $processRecordService->addRecord($record_data);
            if(!$result){
                $message = $processRecordService->error;
                DB::rollBack();
                return ['code'=>100,  'message' => $message ];
            }
        }
        DB::commit();
        #获取节点事件列表
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(
            $start_node['id'],0,ProcessActionEventTypeEnum::NODE_EVENT);
        //触发流程事件
        $event_params = [
            'business_id'       	=> $business_id,
            'process_id'        	=> $process_id,
            'process_category'  	=> $process_category,
            'node_id'           	=> $start_node['id'],
        ];
        $this->triggerNodeEvent($event_list,$event_params);
        return ['code'=>200,  'message' => "流程发起成功！" ];
    }

    /**
     * @desc 客户端审核提交
     * @param $process_record_id
     * @param $process_record_data
     * @return array
     */
    public function updateProcessRecord($process_record_id, $process_record_data){
        if(!isset($process_record_data['node_action_result_id'])){
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
        //获取结果的流转
        $where['process_id'] = $process_record_data['process_id'];
        $where['node_action_result_id'] = $process_record_data['node_action_result_id'];

        if(!$transition = app(ProcessTransitionService::class)->getTransitionByResult($where)){
            Loggy::write('error',"获取流转失败！",$where);
            return ['code'=>200,  'message' => "流程发起成功！" ];
        }
        $process_record_data['action_result_status'] = $transition['status'];
        $event_list =  app(ProcessActionEventService::class)->getActionEventListWithType(
            0,$process_record_data['node_action_result_id'],ProcessActionEventTypeEnum::ACTION_RESULT_EVENT);
        //触发流程事件
        $this->triggerResultEvent($event_list,$process_record_data);


        if(1< $transition['status'] || $transition['next_node'] == 0){ //节点已结束或终止
            return ['code'=>202,  'message' => "流程发起成功！"];

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
        if ($recode_list === false){
            return ['code' => 100,'message' => $processRecordService->error];
        }
        return ['code' => 200,'message' => $processRecordService->message,'data' => $recode_list];
    }


    /**
     * @desc  添加下一个待审记录节点。
     * @param $current_data
     * @param $next_node_id
     * @return mixed
     */
    public function addNextProcessRecord($current_data,$next_node_id){
        if(!isset($current_data['process_id'])){
            $message = "未收到流程ID!";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        $node = OaProcessNodeRepository::getOne(['process_id' => $current_data['process_id'],'id' => $next_node_id]);
        if(!$node){
            $message = "获取下一节点失败";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        if (!$operator_list = OaProcessNodeEventPrincipalsRepository::getList(['node_id' => $node['id'],'principal_iden' => ProcessPrincipalsEnum::EXECUTOR])){
            $message = '该流程节点没有动作执行人，流程发起失败';
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        DB::beginTransaction();
        foreach ($operator_list as $operator){
            $record_data = [
                'business_id'       	=> $current_data['business_id'],
                'process_id'        	=> $current_data['process_id'],
                'process_category'  	=> $current_data['process_category'],
                'node_id'           	=> $node['id'],
                'position'           	=> $node['position'],
                'operator_id'       	=> $operator['principal_id'],
            ];
            //创建流程节点
            $processRecordService = new ProcessRecordService();
            $result = $processRecordService->addRecord($record_data);
            if(!$result){
                $message = $processRecordService->error;
                DB::rollBack();
                return ['code'=>100,  'message' => $message ];
            }
        }
        DB::commit();
        return [
            'business_id'       	=> $current_data['business_id'],
            'process_id'        	=> $current_data['process_id'],
            'process_category'  	=> $current_data['process_category'],
            'node_id'           	=> $node['id'],
        ];
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
        //返回的结构'receiver_id','receiver_name','receiver_mobile','receiver_email'
        if(empty($stakeholders)){
            Loggy::write('error',"没有事件参与人,node_id::" .$event_params['node_id'] );
            return false;
        }
//        //定义事件消息类型 //TODO 这里是不是要升级为配置，或数据表
//        $message_type = [
//            ProcessPrincipalsEnum::EXECUTOR => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
//            ProcessPrincipalsEnum::AGENT => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
//            ProcessPrincipalsEnum::STARTER => ProcessEventMessageTypeEnum::STATUS_NOTICE,
//            ProcessPrincipalsEnum::SUPERVISOR => ProcessEventMessageTypeEnum::STATUS_NOTICE
//        ];
//        //初始化事件的数据。
//        $send_data = [
//            'business_id'       	=> $event_params['business_id'],
//            'process_id'        	=> $event_params['process_id'],
//            'process_category'  	=> $event_params['process_category'],
//            'node_id'           	=> $event_params['node_id'],
//            'employee_id'           => $event_params['operator_id'],
//        ];

        //通过 process_id 获取流程名称。NODE 获取动作名称。
        $send_data['process_full_name'] = app(ProcessNodeService::class)->getProcessNodeFullName(
            $event_params['business_id'],$event_params['node_id']
        );
        $action_result = OaProcessNodeActionsResultViewRepository::getOne(['id' => $event_params['node_action_result_id']]);
        if (!$action_result){
            Loggy::write('error',"节node_action_result_id 数值不对！ ",$event_params['node_action_result_id'] );
            //return false;
            $action_result['name']='';
        }
        if($event_params['action_result_status']>1){
            $send_data['precess_result'] = "已经审核结束，结果是：".$action_result['action_result_name'];
        }else{
            $send_data['precess_result'] = "仍在审核中，当前结果是：".$action_result['action_result_name'];
        }
        //邮件标题
        $send_data['title'] = "你的". $send_data['process_full_name']['process_name'] . "审核进展！";
        //跳转链接
        $send_data['link_url'] = '';
        foreach($event_list as $event){
            foreach ($stakeholders as $receiver){
                if ($receiver['receiver_iden'] != $event['principals_type']){
                    continue;
                }
                $event_data = $send_data;
                $event_data['receiver'] = $receiver;
                $event_data['event_type'] =  $event['event_type'];
                $event_data['business_id']           =  $event_params['business_id'];
                $event_data['process_category']      =  $event_params['process_category'];
                $event_data['event_defined_type']    =  $event['event_defined_type'];
                //所以，到这里，还是要具体获取这些人的ID
                if(ProcessEventEnum::DINGTALK_EMAIL ==  $event['event_defined_type']){
                    event(new SendDingTalkEmail($event_data));
                }
                if(ProcessEventEnum::SMS ==  $event['event_defined_type']){
                    event(new SendSiteMessage($event_data));
                }
                if(ProcessEventEnum::SITE_MESSAGE ==  $event['event_defined_type']){
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
//        $message_type = [
//            ProcessPrincipalsEnum::EXECUTOR => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
//            ProcessPrincipalsEnum::AGENT => ProcessEventMessageTypeEnum::EXCUTE_NOTICE,
//            ProcessPrincipalsEnum::STARTER => ProcessEventMessageTypeEnum::STATUS_NOTICE,
//            ProcessPrincipalsEnum::SUPERVISOR => ProcessEventMessageTypeEnum::STATUS_NOTICE
//        ];
//        //初始化事件的数据。
//        $send_data = [
//                'business_id'       	=> $event_params['business_id'],
//                'process_id'        	=> $event_params['process_id'],
//                'process_category'  	=> $event_params['process_category'],
//                'node_id'           	=> $event_params['node_id'],
//                'employee_id'           => $event_params['operator_id'],
//            ];
        //通过 process_id 获取流程名称。NODE 获取动作名称。
        $send_data['process_full_name'] = app(ProcessNodeService::class)->getProcessNodeFullName(
            $event_params['process_id'],$event_params['node_id']
        );
        //邮件标题
        $send_data['title'] = $send_data['process_full_name']['process_name'] . "有一项审核要处理！";
        //跳转链接
        $send_data['link_url'] = config('process.link_url')[app()['env']];

        //此处为节点事件的执行，只针对事件通知的人群
        foreach($event_list as $event){
           foreach ($stakeholders as $receiver){
               if ($receiver['receiver_iden'] != $event['principals_type']){
                   continue;
               }
               $event_data = $send_data;
               $event_data['receiver']              = $receiver;
               $event_data['event_type']            =  $event['event_type'];
               $event_data['business_id']           =  $event_params['business_id'];
               $event_data['process_category']      =  $event_params['process_category'];
               $event_data['event_defined_type']    =  $event['event_defined_type'];
               //所以，到这里，还是要具体获取这些人的ID
               if(ProcessEventEnum::DINGTALK_EMAIL ==  $event['event_defined_type']){
                   Loggy::write('process',' 触发了发送【邮件】事件！发送人：'.$receiver['receiver_name']);
                   event(new SendDingTalkEmail($event_data));
               }
               if(ProcessEventEnum::SMS ==  $event['event_defined_type']){
                   Loggy::write('process',' 触发了发送【短信】事件！发送人：'.$receiver['receiver_name']);
                   event(new SendFlowSms($event_data));
               }
               if(ProcessEventEnum::SITE_MESSAGE ==  $event['event_defined_type']){
                   Loggy::write('process',' 触发了发送【站内信】事件！发送人：'.$receiver['receiver_name']);
                   event(new SendSiteMessage($event_data));
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
    public function getNodeListByUserId($user_id,$page = 1,$page_num = 20){
        $processRecordService = new ProcessRecordService();
        $recode_list = $processRecordService->getNodeListByUserId($user_id,$page,$page_num);
        if ($recode_list == false){
            return ['code' => 100,'message' => $processRecordService->error];
        }
        return ['code' => 200,'message' => $processRecordService->message,'data' => $recode_list];
    }

    /**
     * 获取业务流程进度，例如：待审核(已审核0步/共3步)
     * @param $business_id
     * @param $process_category
     * @param int $employee_id  OA员工ID
     * @return array
     */
    public function getBusinessProgress($business_id, $process_category,$employee_id){
        $processRecordService = new ProcessRecordService();
        $result = $processRecordService->getBusinessProgress($business_id,$process_category,$employee_id);
        #流程进度，例如
        $progress['process_progress']   = $processRecordService->error;
        $progress['permission']         = ProcessActionPermissionEnum::NO_PERMISSION;
        $progress['process_record_id']  = 0;#流程记录ID
        if ($result !== false){
            $progress['process_progress']   = $result['process_progress'];
            $progress['permission']         = $result['permission'];
            $progress['process_record_id']  = $result['process_record_id'];
        }
        return $progress;
    }

    /**
     * 获取操作结果列表
     * @param $process_record_id
     * @return array|null
     */
    public function getActionResultList($process_record_id){
        $actions_result_column = ['node_actions_result_id','actions_result_name'];
        #如果查看的人是操作人，返回这个操作列表
        if (!$action_result_list = OaProcessRecordActionsResultViewRepository::getList(['record_id' => $process_record_id],$actions_result_column)){
            $action_result_list = [];
        }
        return $action_result_list;
    }

    /**
     * 更新业务审核状态
     * @param $business_id
     * @param $audit_status
     * @param $process_category
     * @return array|bool
     */
    public function updateBusinessAuditStatus($business_id, $audit_status, $process_category){
        $config_data  = config('process.process_perform_list');
        if (!isset($process_category)){
            return [];
        }
        list($class,$function) = $config_data[$process_category];
        try{
            $target_object = app()->make($class);
        }catch (\Exception $e){
            Loggy::write('error',$e->getMessage());
            $this->setError('获取失败!');
            return false;
        }
        if(!method_exists($target_object,$function)){
            $this->setError('函数'.$target_object ."->".$function. '不存在，获取失败!');
            return false;
        }
        $result = $target_object->$function($business_id,$audit_status);
        $this->setError($target_object->error);
        $this->setMessage($target_object->message);
        return $result;
    }

    /**
     * 获取业务详情流程
     * @param $business_details
     * @param $process_category
     * @param $employee_id
     * @return array|bool
     */
    public function getBusinessDetailsProcess($business_details, $process_category, $employee_id){
        #获取流程进度
        $progress = $this->getProcessRecordList(['business_id' => $business_details['id'],'process_category' => $process_category]);
        if (100 == $progress['code']){
            $this->setError($progress['message']);
            return false;
        }
        #获取流程权限
        $process_permission = $this->getBusinessProgress($business_details['id'],$process_category,$employee_id);
        $this->setMessage('获取成功！');
        return [
            'details'               => $business_details,
            'progress'              => $progress['data'],
            'process_permission'    => $process_permission,
            #获取可操作的动作结果列表
            'action_result_list'    => $this->getActionResultList($process_permission['process_record_id'])
        ];
    }
}