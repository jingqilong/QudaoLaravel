<?php
namespace App\Services\Oa;


use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaDepartmentRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeEventPrincipalsRepository;
use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;

/**
 * Class ProcessActionPrincipalsService
 * @desc 流程相关人，获取部门，联动员工，选择后，获得相关人ID。 codeBy:bardo
 * @package App\Services\Oa
 *
 */
class ProcessActionPrincipalsService extends BaseService
{
    protected $departmentService;

    /**
     * ProcessActionPrincipalsService constructor.
     */
    public function __construct()
    {
        $this->departmentService  = new DepartmentService();

    }

    /**
     * 使用分页的时候，去除多余的字段
     * @param $list
     * @return mixed
     */
    public function removePagingField($list){
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        return $list;
    }

    /**
     * @desc 获取员工列表
     * @param $department_id
     * @return mixed
     */
    public function getEmployeeList($department_id){
        $column = ['id','real_name','mobile'];
        if (!$employee_list = OaEmployeeRepository::getList(['department_id'=>$department_id],$column,'id','asc')){
            $this->setError('获取失败！');
            return false;
        }
        $employee_list = $this->removePagingField($employee_list);
        if (empty($employee_list['data'])){
            $this->setMessage('该部门暂无员工！');
            return $employee_list;
        }
        return $employee_list;
    }

    /**
     * @desc  创建参与人
     * @param $node_action_id
     * @param $principal_iden
     * @param $principal_id
     * @return mixed
     */
    public function createPrincipal($node_action_id,$principal_iden,$principal_id){
        if (OaProcessActionPrincipalsRepository::exists(['node_action_id'=>$node_action_id,'principal_iden'=>$principal_iden,'principal_id'=>$principal_id])){
            $this->setError('此参与人已存在！');
            return false;
        }$add_user = [
            'node_action_id'      => $node_action_id,
            'principal_iden'      => $principal_iden,
            'principal_id'     => $principal_id,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (!OaProcessActionPrincipalsRepository::getAddId($add_user)){
            $this->setError('参与人添加失败！');
            return false;
        }
        $this->setMessage('参与人添加成功！');
        return true;
    }

    /**
     * @desc 更新联系人
     * @param $id
     * @param $node_action_id
     * @param $principal_iden
     * @param $principal_id
     * @return mixed
     */
    public function updatePrincipal($id,$node_action_id,$principal_iden,$principal_id){
        if (!OaEmployeeRepository::exists(['id' => $principal_id])){
            $this->setError('参与人不存在！');
            return false;
        }
        $upd_arr = [
            'node_action_id'      => $node_action_id,
            'principal_iden'      => $principal_iden,
            'principal_id'     => $principal_id,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (!OaProcessActionPrincipalsRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * @desc 更新参与人
     * @param $id
     * @return mixed
     */
    public function deletePrincipal($id){
        if (!OaProcessActionPrincipalsRepository::exists(['id' => $id])){
            $this->setError('参与人不存在！');
            return false;
        }
        if (OaProcessActionPrincipalsRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * @param $node_action_id
     * @param int $principal_iden
     * @return mixed
     * @desc 根据指定的action_id 以及 $principal_type 获取相关人列表
     */
    public function getPrincipalList($node_action_id,$principal_iden){
        $where = ['node_action_id' => $node_action_id];
        if(!empty($principal_iden)){
            $where ['principal_iden']=$principal_iden;
        }
        $column = ['id', 'node_action_id', 'principal_id','principal_iden', 'created_at', 'updated_at'];
        if (!$principal_list = OaProcessActionPrincipalsRepository::getList($where,$column,'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        $principal_list = $this->removePagingField($principal_list);
        if (empty($principal_list['data'])){
            $this->setMessage('暂无数据!');
            return $principal_list;
        }
        //TODO 这里最好改为视图。
        $employee_ids = array_column($principal_list['data'],'principal_id');
        $employee_list = OaEmployeeRepository::getAllList(['id'=>['in',$employee_ids]],['id','real_name','department_id'],'id','asc');
        foreach($principal_list['data'] as &$principal){
            $principal['principal_iden_label']  = ProcessPrincipalsEnum::getPprincipalLabel($principal['principal_iden']);
            $principal['real_name']             = '';
            foreach($employee_list as $employee){
                if ($employee['id']== $principal['principal_id']){
                    $principal['real_name']             = $employee['real_name'];
                    $principal['principal_departments'] = OaDepartmentRepository::getDepartmentPath($employee['department_id']);
                    break;
                }
            }
            $principal['created_at'] = empty($principal['created_at']) ? '' : date('Y-m-d H:i:s',$principal['created_at']);
            $principal['updated_at'] = empty($principal['updated_at']) ? '' : date('Y-m-d H:i:s',$principal['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $principal_list;
    }

    /**
     * @desc 通过$business_id获取发起人信息 的统一接口，后续增加，只要在类中增加即楞
     * @param $business_id
     * @param $process_category
     * @return mixed
     */
    public function getStartUserInfo($business_id,$process_category){
        //1	成员升级         //2	活动报名        //3	项目对接        //4	贷款预约
        //5	企业咨询        //6	看房预约        //7	医疗预约        //8	精选生活预约
        //从配置文件读取方法  //TODO 具体的方法仍未实现 ,这样，后续变化不要到这里改代码
        $config_data  = config('process.process_starter');
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
        $start_user_id = $target_object->$function($business_id);
        return MemberBaseRepository::find($start_user_id);
    }

    /**
     * @desc 获取节点事件参与人的信息
     * @param $node_id
     * @param $business_id
     * @param $process_category
     * @return mixed
     */
    public function getNodeEventPrincipals($node_id,$business_id,$process_category){
        //获取所有的参与人
        if(!$stakeholders = OaProcessNodeEventPrincipalsRepository::getAllList(['node_id'=> $node_id])){
            Loggy::write("error","本节点缺少参与人！Node_id::" . $node_id);
            return [];
        }
        $principal_list = [];
        foreach ($stakeholders as $receivers) {
            //如果是发起人，则先获取发起人的相关信息
            $event_principal['receiver_iden'] = $receivers['principal_iden'];
            //获取员工信息
            $principal = OaEmployeeRepository::getOne(['id' => $receivers['principal_id']]);
            $event_principal['receiver_name']   = $principal['real_name'];
            $event_principal['receiver_id']     = $principal['id'];
            $event_principal['receiver_mobile'] = $principal['mobile'];
            $event_principal['receiver_email']  = $principal['email'];
            $principal_list[] = $event_principal;
        }
        $start_user = $this->getStartUserInfo($business_id,$process_category);
        $principal_list[] = [
            'receiver_iden' => ProcessPrincipalsEnum::STARTER,
            'receiver_name' => $start_user['ch_name'],
            'receiver_id'   => $start_user['id']
        ];
        return $principal_list;
    }

    /**
     * @desc 获取节点动作结果事件参与人的信息
     * @param $node_action_result_id
     * @param $business_id
     * @param $process_category
     * @return mixed
     */
    public function getResultEventPrincipals($node_action_result_id,$business_id,$process_category){
        //获取所有的参与人
        if (!$node_action_id = OaProcessNodeActionsResultRepository::getField(['id' => $node_action_result_id],'node_action_id')){
            Loggy::write("error","流程节点动作结果查找失败！！node_action_result_id::" . $node_action_result_id);
            return [];
        }
        if(!$stakeholders = OaProcessNodeEventPrincipalsRepository::getAllList(['node_action_id'=> $node_action_id])){
            Loggy::write("error","本节点缺少参与人！node_action_result_id::" . $node_action_result_id);
            return [];
        }
        $principal_list = [];
        foreach ($stakeholders as $receivers) {
            //如果是发起人，则先获取发起人的相关信息
            $event_principal['receiver_iden'] = $receivers['principal_iden'];
            //获取员工信息
            $principal = OaEmployeeRepository::getOne(['id' => $receivers['principal_id']]);
            $event_principal['receiver_name']   = $principal['real_name'];
            $event_principal['receiver_id']     = $principal['id'];
            $event_principal['receiver_mobile'] = $principal['mobile'];
            $event_principal['receiver_email']  = $principal['email'];
            $principal_list[] = $event_principal;
        }
        $start_user = $this->getStartUserInfo($business_id,$process_category);
        $principal_list[] = [
            'receiver_iden'     => ProcessPrincipalsEnum::STARTER,
            'receiver_name'     => $start_user['ch_name'],
            'receiver_id'       => $start_user['id'],
            'receiver_mobile'   => $start_user['mobile'],
            'receiver_email'    => $start_user['email']
        ];
        return $principal_list;
    }



}
            