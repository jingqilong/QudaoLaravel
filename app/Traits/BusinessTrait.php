<?php
namespace App\Traits;

use App\Enums\ProcessActionEnum;
use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Services\Oa\ProcessNodeService;
use App\Services\Oa\ProcessRecordService;
use Illuminate\Support\Facades\Auth;
use Tolawho\Loggy\Facades\Loggy;
use App\Events\SendDingTalkEmail;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;

/**
 * Class BusinessTrait
 * @package App\Traits
 * @desc 这是给业务程序来使用的trait
 */
class BusinessTrait
{

    /**
     * @desc 发起流程请求
     * @param $business_id    ,业务ID
     * @param $process_type   ,业务类型
     * @param $by_oa_user     ,发起人类型
     * @param $auto_audit     ,是否自动审核
     * @return mixed
     */
    public function addNewProcessRecord($business_id,$process_type,$by_oa_user,$auto_audit=false){
        //是否发起后，直接通过，并请求下一节点
        $has_next = false;
        //获取流程定义ID
        $process_id = OaProcessDefinitionRepository::getOne(['category_id'=>$process_type],['id',]);
        if(!$process_id){
            $message = "此类流程未定义，请联系系统管理员，定义后再处理";
            Loggy::write("error",$message);
            return ['code'=>100,  'message' => $message ];
        }
        $node_id =1;
        $auditors_id = 0;
        $audit_result = 0;
        $audit_opinion = "";
        if($by_oa_user){ //如果发起人是从OA中来的。那么，我们要查一下此人的审核位置。
            $auth = Auth::guard('oa_api');
            $user_id = $auth->id;
            $oaProcessNodeService =new ProcessNodeService();
            //查找我负责审核的节点  //TODO 这个函数未完成
            $node= $oaProcessNodeService->getStartNodeByUser($process_id,$user_id);
            if(1 != $node['position']){
                $node_id = $node['id'];
                //我要transition中，审核 ACCEPT 的走向。
                $has_next= true;
                //自动设为审核完成
                if(true == $auto_audit){
                    $auditors_id = $user_id;
                    $audit_result = ProcessActionEnum::ACCEPT;
                }
                $audit_opinion = "";
            }
        }
        $processRecordService = new ProcessRecordService();

        $result = $processRecordService->addRecord($process_id,$business_id, $node_id,$auditors_id,$audit_result,$audit_opinion);
        if(!$result){
            $message = $processRecordService->error;
            return ['code'=>100,  'message' => $message ];
        }
        $event_data = [
            'business_id'=> $business_id,
            'process_id'=> $process_id,
            'node_id'=> $node_id,
            'auditors_id'=> $auditors_id,
            'audit_result'=> $audit_result,
            'audit_opinion'=> $audit_opinion
        ];
        //触发流程事件
        $this->triggerEvent($event_data);
        if($has_next){ //如果是审核人添加，自己的节点自动通过以后，自动触发下一节点。
            $next_event_data = $this->addNextProcessRecord($business_id,$process_id, $node_id, $auditors_id, $audit_result);
            if($next_event_data){
                $this->triggerEvent($next_event_data);
            }
        }
        return ['code'=>200,  'message' => "流程发起成功！" ];
    }

    /**
     * @desc 客户端审核提交
     * @param $business_id
     * @param $process_id
     * @param $node_id
     * @param $auditors_id
     * @param $audit_result
     * @param string $audit_opinion
     * @return array
     */
    public function updateProcessRecord($business_id, $process_id, $node_id, $auditors_id, $audit_result, $audit_opinion = ''){
        //是否发起后，直接通过，并请求下一节点
        $has_next = false;
        $processRecordService = new ProcessRecordService();
        //  function addRecord($business_id, $process_id, $node_id, $auditors_id, $audit_result, $audit_opinion = '')
        $result = $processRecordService->addRecord($process_id,$business_id, $node_id,$auditors_id,$audit_result,$audit_opinion);
        if(!$result){
            $message = $processRecordService->error;
            return ['code'=>100,  'message' => $message ];
        }
        $event_data = [
            'business_id'=> $business_id,
            'process_id'=> $process_id,
            'node_id'=> $node_id,
            'auditors_id'=> $auditors_id,
            'audit_result'=> $audit_result,
            'audit_opinion'=> $audit_opinion
        ];
        //触发流程事件
        $this->triggerEvent($event_data);
        if($has_next){  //审核完成后，增加新节点   //TODO 问题：我如何判断当前已经结束？
            $next_event_data  =  $this->addNextProcessRecord($business_id,$process_id, $node_id, $auditors_id, $audit_result);
            if($next_event_data)
            $this->triggerEvent($next_event_data);
        }
        return ['code'=>200,  'message' => "流程发起成功！" ];

    }

    /**
     * @desc 获取当前业务的审核列表
     * @param $business_id
     */
    public function getProcessRecordList($business_id){
        //TODO 这里调用审核详情

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
     * @param $event_data
     * @desc 流程触发事件总接口函数
     * @return bool
     */
    public function triggerEvent($event_data){
        //这里获取所有的事件列表
        $event_list = [];
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