<?php


namespace App\Services\Oa;


use App\Enums\CommonAuditStatusEnum;
use App\Enums\ProcessCommonStatusEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionsResultViewRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use App\Traits\BusinessTrait;
use Illuminate\Support\Facades\Auth;
use Tolawho\Loggy\Facades\Loggy;

class ProcessPerformService extends BaseService
{
    use BusinessTrait;

    /**
     * 提交操作结果
     * @param $request
     * @return bool
     */
    public function submitOperationResult($request)
    {
        $employee = Auth::guard('oa_api')->user();
        #检查有效性
        if (!$process_record = OaProcessRecordRepository::getOne(['id' => $request['process_record_id'] ,'operator_id' => $employee->id])){
            $this->setError('流程记录不存在！');
            return false;
        }
        #检查流程步骤是否有下一步，如果有下一步，则继续进行流程，如果没有下一步，则结束流程，并更新业务的审核结果
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_record['process_id']])){
            $this->setError('该业务流程不存在！');
            return false;
        }
        if (ProcessCommonStatusEnum::DISABLE == $process['status']){
            $this->setError('该业务流程已关闭，不能进行操作！');
            return false;
        }
        $process_record_data = [
            'business_id'               => $process_record['business_id'],
            'process_id'                => $process_record['process_id'],
            'process_category'          => $process_record['process_category'],
            'node_id'                   => $process_record['node_id'],
            'node_action_result_id'     => $request['node_actions_result_id'],
            'operator_id'               => $employee->id,
            'note'                      => $request['note'] ?? ''
        ];
        $update_process_record_result = $this->updateProcessRecord($request['process_record_id'],$process_record_data);
        if (100 == $update_process_record_result['code']){
            $this->setError($update_process_record_result['message']);
            return false;
        }
        if (200 == $update_process_record_result['code']){
            $this->setMessage('操作成功！');
            return true;
        }
        #如果审核的是最后一个节点，则更改业务审核状态
        if (202 == $update_process_record_result['code']){
            if ($action_result= OaProcessNodeActionsResultViewRepository::getOne(['id' => $request['node_actions_result_id']])){
                $audit_status = $action_result['action_result_name'] == '同意' ? CommonAuditStatusEnum::PASS : CommonAuditStatusEnum::NO_PASS;
                Loggy::write('debug','提交审核结果action_result_name:'.$action_result['action_result_name'].'，审核结果:'.$audit_status);
                $upd_result = $this->updateBusinessAuditStatus($process_record['business_id'],$audit_status,$process_record['process_category']);
                if ($upd_result == false){
                    Loggy::write('process',$this->error);
                }
            }
            $this->setMessage('操作成功！');
            return true;
        }
        $this->setError('操作失败！');
        return false;
    }

    /**
     * 获取我的审核列表
     * @param $request
     * @return bool|mixed
     */
    public function getMyAuditList($request)
    {
        $employee = Auth::guard('oa_api')->user();
        $result = $this->getNodeListByUserId($employee->id);
        if (100 == $result['code']){
            $this->setError($result['message']);
            return false;
        }
        $this->setMessage($result['message']);
        return $result['data'];
    }
}