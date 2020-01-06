<?php


namespace App\Services\Oa;


use App\Enums\ProcessCommonStatusEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use App\Traits\BusinessTrait;
use Illuminate\Support\Facades\Auth;

class ProcessPerformService extends BaseService
{
    use BusinessTrait;

    public function submitOperationResult($request)
    {
        $employee = Auth::guard('oa_api')->user();
        #检查有效性
        if (!$process_record = OaProcessRecordRepository::exists(['id' => $request[''] ,'operator_id' => $employee->id])){
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
            'node_actions_result_id'    => $request['node_actions_result_id'],
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
            $this->updateBusinessAuditStatus($process_record['business_id'],'',$process_record['process_category']);
            //TODO
        }
    }
}