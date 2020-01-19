<?php
namespace App\Services\Oa;

use App\Enums\ProcessActionPermissionEnum;
use App\Enums\ProcessCategoryEnum;
use App\Enums\ProcessRecordStatusEnum;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionsResultViewRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Tolawho\Loggy\Facades\Loggy;

class ProcessRecordService extends BaseService
{
    use HelpTrait;
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
        $check_process = OaProcessDefinitionRepository::isEnabled($process_record_data['process_id']);
        if ($check_process['code'] == 100){
            Loggy::write('process','流程已被禁用，无法添加该流程！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤：'.$process_record_data['node_id']);
            $this->setError('当前流程已被禁用，无法添加该流程！');
            return false;
        }
        if (!isset($process_record_data['path'])){
            if (!$last_record = OaProcessRecordRepository::getOrderOne(
                ['process_id' => $process_record_data['process_id'],'business_id' => $process_record_data['business_id']],
                'id',
                'desc'
            )){
                Loggy::write('process','该业务还没有添加流程，请先添加一个流程！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤node_id：'.$process_record_data['node_id']);
                $this->setError('该业务还没有添加流程，请先添加一个流程！');
                return false;
            }
            $process_record_data['path']        = $last_record['path']  . ',' . $process_record_data['node_id'];
        }
        if (!OaProcessNodeRepository::exists(['process_id' => $process_record_data['process_id'],'id' => $process_record_data['node_id']])){
            Loggy::write('process','该流程下不存在此节点！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['process_id'].'，执行步骤node_id：'.$process_record_data['node_id']);
            $this->setError('该流程下不存在此节点！');
            return false;
        }
        if (!$record_id = OaProcessRecordRepository::getAddId($process_record_data)){
            Loggy::write('process','流程进度记录失败！业务ID：'.$process_record_data['business_id'].'，流程ID：'.$process_record_data['business_id'].'，执行步骤node_id：'.$process_record_data['node_id']);
            $this->setError('流程进度记录失败！');
            return false;
        }
        #更新路径
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
        $process_record_data['operation_at']    = time();
        $process_record_data['updated_at']      = time();
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
     * @return bool|null
     */
    public function getRecordList($where)
    {
        if (empty($where)){
            $where=['id' => ['>',0]];
        }
        if (!$action_list = OaProcessRecordRepository::getList($where,['*'],'id','asc')){
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
            $value['operation_at']  = empty($value['operation_at']) ? '' : date('Y-m-d H:m:s',$value['operation_at']);
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
        $column= ['id','node_id','node_action_result_id','operator_id','note','status','operation_at'];
        if (!$recode_list = OaProcessRecordRepository::getAllList($where,$column,'created_at','asc')){
            $this->setMessage('暂无数据！');
            return [];
        }
        $node_ids               = array_column($recode_list,'node_id');
        $node_list              = OaProcessNodeRepository::getAllList(['id' => ['in',$node_ids]]);
        $node_action_result_ids = array_column($recode_list,'node_action_result_id');
        $node_action_result_list= OaProcessNodeActionsResultViewRepository::getAllList(['id' => ['in',$node_action_result_ids]]);
        $recode_list            = EmployeeService::getListOperationByName($recode_list,['operator_id' => 'operator']);
        foreach ($recode_list as &$recode){
            foreach ($node_list as $node){
                if ($recode['node_id'] == $node['id']){
                    $recode['node_name'] = $node['name'];break;
                }
            }
            $recode['status_label'] = ProcessRecordStatusEnum::getLabel($recode['status']);
            $recode['node_action_result_label'] = '';
            foreach ($node_action_result_list as $value){
                if ($recode['node_action_result_id'] == $value['id']){
                    $recode['node_action_result_label']   = $value['action_result_name'];break;
                }
            }
            $recode['operation_at'] = empty($recode['operation_at']) ? '' : date('Y-m-d H:i:s',$recode['operation_at']);
            unset($recode['node_action_result_id'],$recode['node_id'],$recode['operator_id']);
        }
        $this->setMessage('记录列表获取成功！');
        return $recode_list;
    }

    /**
     * 仪表板中的我的审核列表
     * @param $user_id
     * @return mixed
     */
    public function getNodeListByUserId($user_id)
    {
        $where = ['operator_id' => $user_id,'node_action_result_id' => ['in',[0,null]],'status' => ProcessRecordStatusEnum::DEFAULT];
        $column= ['id','business_id','process_id','process_category','position','created_at'];
        if (!$recode_list = OaProcessRecordRepository::getList($where,$column,'created_at','desc')){
            $this->setError('获取失败！');
            return false;
        }
        $recode_list = $this->removePagingField($recode_list);
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
            #给业务分类列表建立索引
            $business_list[$process_category] = $this->getAllBusinessList($business_ids,$process_category);
        }
        $result = [];
        foreach ($recode_list['data'] as $key => &$recode){
            $list = $business_list[$recode['process_category']] ?? [];
            foreach ($list as $value){
                if ($recode['business_id'] == $value['id']){
                    $result[$key]['process_category']= $recode['process_category'];
                    $result[$key]['business_id']    = $recode['business_id'];
                    $result[$key]['business_name']  = $value['name'];
                    $result[$key]['member_name']    = $value['member_name'];
                    $result[$key]['member_mobile']  = $value['member_mobile'];
                    $result[$key]['created_at']     = date('Y-m-d H:i:s',$recode['created_at']);
                    //TODO 如需要更多参数，在此添加
                }
            }
        }
        $recode_list['data'] = array_values($result);
        $this->setMessage('获取成功！');
        return $recode_list;
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

    /**
     * 获取业务流程进度
     * @param $business_id
     * @param $process_category
     * @param int $employee_id  OA员工ID
     * @return mixed
     */
    public function getBusinessProgress($business_id, $process_category,$employee_id)
    {
        $where = ['business_id' => $business_id,'process_category' => $process_category];
        $column= ['*'];
        if (!$recode_list = OaProcessRecordRepository::getAllList($where,$column)){
            $this->setError('该业务未开启流程！');
            return false;
        }
        $process_id = reset($recode_list)['process_id'];
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('该业务流程不存在！');
            return false;
        }
        $completed      = [];#已完成步骤
        $no_completed   = [];#未完成步骤
        foreach ($recode_list as $value){
            if (!empty($value['node_action_result_id'])){
                $completed[] = $value;continue;
            }
            if (empty($value['node_action_result_id'])){
                $no_completed[] = $value;
            }
        }
        $permission         = ProcessActionPermissionEnum::NO_PERMISSION;#表示当前是否有权限操作，0不能审核，1可以审核
        $process_record_id  = 0;#需要审核时，审核记录ID
        $progress           = '待审核';
        foreach ($no_completed as $item){
            if ($item['operator_id'] == $employee_id){
                if ($item['status'] == ProcessRecordStatusEnum::STOPPED){
                    $progress = '已停止';break;
                }
                $permission         = ProcessActionPermissionEnum::PERMISSION;
                $progress           = '审核中';
                $process_record_id  = $item['id'];break;
            }
        }
        #流程进度
        if (empty($no_completed)){
            $progress = '已完成';
        }
//        $progress = empty($completed) ? '待审核' : (!empty($no_completed) ? '审核中' : '已完成');
        $progress .= '(已审核 ' . count($completed) . ' 步/共 '. $process['step_count'] .' 步)';
        $this->setMessage('获取成功！');
        return ['process_progress' => $progress,'permission' => $permission,'process_record_id' => $process_record_id];
    }
}
            