<?php
namespace App\Services\Oa;


use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaProcessActionPrincipalsRepository;
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
     * @desc 获取部门表列
     * @param $page
     * @param $pageNum
     * @return mixed
     *
     */
    public function getDepartmentList($page,$pageNum){
        return $this->departmentService->getDepartList($page,$pageNum);
    }

    /**
     * @desc 获取员工列表
     * @param $department_id
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    public function getEmployeeList($department_id,$page,$pageNum){
        return OaEmployeeRepository::getList(['department_id'=>$department_id],$page,$pageNum);
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
        if (!OaProcessActionPrincipalsRepository::exists(['id' => $id])){
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
        if (!OaEmployeeRepository::getUpdId(['id' => $id],$upd_arr)){
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
     * @desc 根据指定的action_id 以及 $principal_type 获取相关人列表
     * @return mixed
     */
    public function getPrincipalList($node_action_id,$principal_iden,$page,$pageNum){
        $where = [];
        if(!empty($node_action_id)){
            $where ['node_action_id']=$node_action_id;
         }
        if(!empty($principal_iden)){
            $where ['principal_iden']=$principal_iden;
        }
        $column = ['id', 'node_action_id', 'principal_id','principal_iden', 'created_at', 'updated_at'];
        if (!$principal_list = OaProcessActionPrincipalsRepository::getList(['id' => ['>',0]],$column,'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        $principal_list = $this->removePagingField($principal_list);
        if (empty($principal_list['data'])){
            $this->setMessage('暂无数据!');
            return $principal_list;
        }
        //TODO 这里最好改为视图。
        $employee_ids ='';
        foreach($principal_list['data'] as $principal){
            if(!empty($employee_ids)){
                $employee_ids.=',';
            }
            $employee_ids .= $principal['principal_id'];
        }
        $employee_list = OaEmployeeRepository::getList(['is'=>['in',$employee_ids]],['id','real_name'],['id'],'asc',1,count($principal_list['data']));
        foreach($principal_list['data'] as &$principal){
            foreach($employee_list as $employee){
                if ($employee['id']== $principal['principal_id']){
                    $principal['real_name'] = $employee['real_name'];
                    break;
                }
            }
        }
        return $employee_list;
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
        return $target_object->$function($business_id);
    }

    /**
     * @desc 获取节点事件参与人的信息
     * @param $business_id
     * @param $node_id
     * @param $process_category
     * @return mixed
     */
    public function getNodeEventPrincipals($business_id,$node_id,$process_category){
        //获取所有的参与人
        if(!$stakeholders = OaProcessNodeEventPrincipalsRepository::getList(['node_id'=> $node_id])){
            Loggy::write("error","本节点缺少参与人！Node_id::" . $node_id);
            return [];
        }
        $principal_list = [];
        foreach ($stakeholders as $receivers) {
            //如果是发起人，则先获取发起人的相关信息
            $principal_list['receiver_iden'] = $receivers['principal_iden'];
            if (ProcessPrincipalsEnum::STARTER == $receivers['principal_iden']) {
                $principal = $this->getStartUserInfo($business_id,$process_category);
                $principal_list['receiver_name'] = $principal['ch_name'];
                $principal_list['receiver_id'] = $principal['id'];
            } else {   //获取员工信息
                $principal = app(OaEmployeeRepository::getOne(['id' => $receivers['principal_id']]));
                $principal_list['receiver_name'] = $principal['m_cname'];
                $principal_list['receiver_id'] = $principal['m_id'];
            }
        }
        return $principal_list;
    }
}
            