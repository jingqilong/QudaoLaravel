<?php
namespace App\Services\Oa;

use App\Enums\ProcessDefinitionStatusEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;

class ProcessRecordService extends BaseService
{
    /**
     * @desc 添加流程进度记录
     * @param array $process_record_data
     * @return bool
     */
    public function addRecord($process_record_data)
    {
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_record_data['process_id']])){
            $this->setError('流程不存在！');
            return false;
        }
        $process_record_data['created_at'] = time();
        $process_record_data['updated_at'] = time();
        if (empty($node_id)){
            if ($process['status'] == ProcessDefinitionStatusEnum::INACTIVE){
                Loggy::write('process','流程已被禁用，无法添加该流程！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤：'.$process_record_data['node_id']);
                $this->setError('当前流程已被禁用，无法添加该流程！');
                return false;
            }

        }else{
            if (!$node = OaProcessNodeRepository::getOne(['process_id' => $process_record_data['process_id'],'id' => $node_id])){
                Loggy::write('process','该流程下不存在此节点！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤：'.$node_id);
                $this->setError('该流程下不存在此节点！');
                return false;
            }
            if (!$last_record = OaProcessRecordRepository::getOrderOne(
                ['process_id' => $process_record_data['process_id'],'business_id' => $process_record_data['business_id']],
                'id',
                'desc'
            )){
                Loggy::write('process','该业务还没有添加流程，请先添加一个流程！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤：'.$node_id);
                $this->setError('该业务还没有添加流程，请先添加一个流程！');
                return false;
            }

            $process_record_data['position']        = $node['position'];
            $process_record_data['node_id']         = $node['id'];
            $process_record_data['path']            = $last_record['path']  . ',' . $node_id;
        }
        if (!OaProcessRecordRepository::getAddId($process_record_data)){
            Loggy::write('process','流程进度记录失败！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['business_id'].'，执行步骤：'.$node_id);
            $this->setError('流程进度记录失败！');
            return false;
        }
        $this->setMessage('流程进度记录成功！');
        return true;
    }

    /**
     * 删除流程记录
     * @param $process_record_id
     * @return bool
     */
    public function deleteRecord($process_record_id)
    {
        if (!OaProcessRecordRepository::exists(['id' => $process_record_id])){
            $this->setError('该流程记录不存在！');
            return false;
        }
        if (!OaProcessRecordRepository::delete(['id' => $process_record_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * @desc 编辑流程记录
     * @param $process_record_id
     * @param $process_record_data
     * @return bool
     */
    public function updateRecord($process_record_id,$process_record_data)
    {
        if (!$action = OaProcessRecordRepository::getOne(['id' => $process_record_id])){
            $this->setError('该流程记录不存在！');
            return false;
        }
        $process_record_data['created_at']    = time();
        $process_record_data['updated_at']    = time();
        if (OaProcessRecordRepository::getUpdId(['id' => $process_record_id],$process_record_data)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * @desc 获取审核记录列表
     * @param $where
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getRecordList($where, $page, $pageNum)
    {
        if (empty($where)){
            $where=['id' => ['>',0]];
        }
        if (!$action_list = OaProcessRecordRepository::getList($where,['*'],'id','asc',$page,$pageNum)){
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
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $action_list;
    }
}
            