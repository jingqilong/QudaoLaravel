<?php
namespace App\Services\Oa;


use App\Enums\ProcessDefinitionEnum;
use App\Repositories\MemberBusinessRecordRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;

class ProcessRecordService extends BaseService
{
    /**
     * 添加流程进度记录
     * @param integer $business_id  业务ID
     * @param integer $process_id   流程ID
     * @param integer $node_id      节点ID【为0时表示给业务添加流程】
     * @return bool
     */
    public function addRecord($business_id, $process_id, $node_id)
    {
        if (!MemberBusinessRecordRepository::exists(['id' => $business_id])){
            $this->setError('业务不存在！');
            return false;
        }
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('流程不存在！');
            return false;
        }
        $add_arr = [
            'business_id'   => $business_id,
            'process_id'    => $process_id,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (empty($node_id)){
            if ($process['status'] == ProcessDefinitionEnum::INACTIVE){
                $this->setError('当前流程已被禁用，无法添加该流程！');
                return false;
            }
            if (OaProcessRecordRepository::exists(['business_id' => $business_id])){
                $this->setError('当前业务已建立审核流程，无法再次添加！');
                return false;
            }
        }else{
            if (!$node = OaProcessNodeRepository::getOne(['process_id' => $process_id,'id' => $node_id])){
                $this->setError('该流程下不存在此节点！');
                return false;
            }
            if (!$last_record = OaProcessRecordRepository::getOrderOne(
                ['process_id' => $process_id,'business_id' => $business_id],
                'position',
                'desc'
            )){
                $this->setError('该业务还没有添加流程，请先添加一个流程！');
                return false;
            }
            if ($last_record['position'] + 1 < $node['position']){
                $this->setError('该步骤前还有' . ($node['position'] - ($last_record['position'] + 1)) . '个步骤未执行，请按照顺序进行！');
                return false;
            }
            $add_arr['position'] = $node['position'];
            $add_arr['node_id']  = $node['id'];
            $add_arr['path']     = $last_record['path']  . ',' . $node_id;
        }
        if (!OaProcessRecordRepository::getAddId($add_arr)){
            $this->setError('流程进度记录失败！');
            return false;
        }
        $this->setMessage('流程进度记录成功！');
        return true;
    }
}
            