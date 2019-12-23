<?php
namespace App\Services\Oa;


use App\Enums\ProcessDefinitionEnum;
use App\Repositories\MemberBusinessRecordRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;

class ProcessRecordService extends BaseService
{
    /**
     * 添加流程进度记录
     * @param int $business_id      业务ID
     * @param int $process_id       流程ID
     * @param int $node_id          节点ID【为0时表示给业务添加流程】
     * @param int $auditors_id      审核人ID
     * @param string $audit_result  审核结果
     * @param string $audit_opinion 审核意见
     * @return bool
     */
    public function addRecord($business_id, $process_id, $node_id, $auditors_id, $audit_result, $audit_opinion = '')
    {
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('流程不存在！');
            return false;
        }
        $add_arr = [
            'business_id'       => $business_id,
            'process_id'        => $process_id,
            'process_category'  => $process['category_id'],
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
        if (empty($node_id)){
            if ($process['status'] == ProcessDefinitionEnum::INACTIVE){
                Loggy::write('process','流程已被禁用，无法添加该流程！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id);
                $this->setError('当前流程已被禁用，无法添加该流程！');
                return false;
            }
            if (OaProcessRecordRepository::exists(['business_id' => $business_id])){
                Loggy::write('process','当前业务已建立审核流程，无法再次添加！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id);
                $this->setError('当前业务已建立审核流程，无法再次添加！');
                return false;
            }
        }else{
            if (!$node = OaProcessNodeRepository::getOne(['process_id' => $process_id,'id' => $node_id])){
                Loggy::write('process','该流程下不存在此节点！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id);
                $this->setError('该流程下不存在此节点！');
                return false;
            }
            if (!$last_record = OaProcessRecordRepository::getOrderOne(
                ['process_id' => $process_id,'business_id' => $business_id],
                'id',
                'desc'
            )){
                Loggy::write('process','该业务还没有添加流程，请先添加一个流程！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id);
                $this->setError('该业务还没有添加流程，请先添加一个流程！');
                return false;
            }
            if ($last_record['position'] + 1 < $node['position']){
                Loggy::write('process','流程添加步骤异常！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id.'，异常原因：该步骤前还有' . ($node['position'] - ($last_record['position'] + 1)) . '个步骤未执行！');
                $this->setError('该步骤前还有' . ($node['position'] - ($last_record['position'] + 1)) . '个步骤未执行，请按照顺序进行！');
                return false;
            }
            $add_arr['position']        = $node['position'];
            $add_arr['node_id']         = $node['id'];
            $add_arr['path']            = $last_record['path']  . ',' . $node_id;
            $add_arr['audit_opinion']   = $audit_opinion;
            $add_arr['audit_result']    = $audit_result;
            $add_arr['auditors_id']     = $auditors_id;
        }
        if (!OaProcessRecordRepository::getAddId($add_arr)){
            Loggy::write('process','流程进度记录失败！业务ID：'.$business_id.'，流程ID：'.$process_id.'，执行步骤：'.$node_id);
            $this->setError('流程进度记录失败！');
            return false;
        }
        $this->setMessage('流程进度记录成功！');
        return true;
    }


    public function getProcessRecodeList($request){
        //
    }
}
            