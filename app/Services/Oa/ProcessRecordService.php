<?php
namespace App\Services\Oa;

use App\Enums\ProcessCategoryEnum;
use App\Enums\ProcessCommonStatusEnum;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionsResultViewRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
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
        if (!OaProcessDefinitionRepository::exists(['id' => $process_record_data['process_id']])){
            $this->setError('流程不存在！');
            return false;
        }
        $process_record_data['created_at'] = time();
        $process_record_data['updated_at'] = time();
        if (empty($node_id)){
            $check_process = OaProcessDefinitionRepository::isEnabled($process_record_data['process_id']);
            if ($check_process['code'] == 100){
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
        if (!OaProcessRecordRepository::exists(['id' => $process_record_id])){
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

    /**
     * 获取当前业务的审核列表
     * @param $request
     * @return array|bool|null
     */
    public function getProcessRecodeList($request)
    {
        if (!isset($request['business_id'])){
            $this->setError('业务ID不能为空！');
            return false;
        }
        if (!isset($request['process_category'])){
            $this->setError('流程类型！');
            return false;
        }
        $where = ['business_id' => $request['business_id'],'process_category' => $request['process_category']];
        $column= ['id','position','node_id','node_action_result_id','operator_id','note','created_at','updated_at'];
        if (!$recode_list = OaProcessRecordRepository::getList($where,$column,'created_at','asc')){
            $this->setMessage('暂无数据！');
            return [];
        }
        $node_action_result_ids = array_column($recode_list,'node_action_result_id');
        $node_action_result_list= OaProcessNodeActionsResultViewRepository::getList(['id' => ['in',$node_action_result_ids]]);
        $operator_list          = EmployeeService::getListOperationByName($recode_list,['operator_id']);
        foreach ($recode_list as &$recode){
            $recode['action_result_name'] = '';
            foreach ($node_action_result_list as $value){
                if ($recode['node_action_result_id'] == $value['id']){
                    $recode['action_result_name'] = $value['action_result_name'];
                }
            }
            $recode['created_at'] = empty($recode['created_at']) ? '' : date('Y-m-d H:i:s',$recode['created_at']);
            $recode['updated_at'] = empty($recode['updated_at']) ? '' : date('Y-m-d H:i:s',$recode['updated_at']);
        }
        $this->setMessage('记录列表获取成功！');
        return $recode_list;
    }

    /**
     * 仪表板中的我的审核列表
     * @param $user_id
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getNodeListByUserId($user_id,$page,$page_num)
    {
        $where = ['operator_id' => $user_id,'node_action_result_id' => ['in',[0,null]]];
        $column= ['*'];
        if (!$recode_list = OaProcessRecordRepository::getList($where,$column,'created_at','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $recode_list = app(HelpTrait::class)->removePagingField($recode_list);
        if (empty($recode_list['data'])){
            $this->setMessage('暂无数据！');
            return $recode_list;
        }
        $business_list = [];//业务列表
        foreach (ProcessCategoryEnum::$data_map as $process_category => $label){
            $business_ids = [];
            foreach ($recode_list['data'] as $recode){
                if ($process_category == $recode['process_category']){
                    $business_ids[] = $recode['business_id'];
                }
            }
            $business_list[$process_category] = $this->getAllBusinessList($business_ids,$process_category);
        }
        foreach ($recode_list['data'] as &$recode){
            $list = $business_list[$recode['process_category']];
            foreach ($business_list as $category => $list){
                if ($recode['process_category'] !== $category){
                    break;
                }
                //foreach ($list)
            }
        }
        return [];
    }

    /**
     * 获取所有流程业务列表
     * @param $business_ids
     * @param $process_category
     * @return array|bool
     */
    public function getAllBusinessList($business_ids, $process_category){
        if (empty($business_ids)){
            return [];
        }
        $config_data  = config('process.process_business_list');
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
        return $target_object->$function($business_ids);
    }
}
            