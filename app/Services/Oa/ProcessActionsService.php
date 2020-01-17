<?php
namespace App\Services\Oa;


use App\Enums\ProcessCommonStatusEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

/**
 * @desc 基础数据，流程动作
 * Class ProcessActionsService
 * @package App\Services\Oa
 */
class ProcessActionsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加动作
     * @param $request
     * @return bool
     */
    public function addAction($request)
    {
        if (OaProcessActionsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'status'        => $request['status'],
            'description'   => $request['description'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除动作
     * @param $action_id
     * @return bool
     */
    public function deleteAction($action_id)
    {
        if (!OaProcessActionsRepository::exists(['id' => $action_id])){
            $this->setError('该动作不存在！');
            return false;
        }
        if (OaProcessNodeActionRepository::exists(['action_id' => $action_id])){
            $this->setError('无法删除已使用的动作！');
            return false;
        }
        if (!OaProcessActionsRepository::delete(['id' => $action_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改动作
     * @param $request
     * @return bool
     */
    public function editAction($request)
    {
        if (!$action = OaProcessActionsRepository::getOne(['id' => $request['action_id']])){
            $this->setError('该事件不存在！');
            return false;
        }
        if ($action['name'] != $request['name'] && OaProcessActionsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'status'        => $request['status'],
            'description'   => $request['description'],
            'updated_at'    => time(),
        ];
        if (OaProcessActionsRepository::getUpdId(['id' => $request['action_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取动作列表
     * @return bool|null
     */
    public function getActionList()
    {
        if (!$action_list = OaProcessActionsRepository::getList(['id' => ['>',0]],['*'],'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        $action_list = $this->removePagingField($action_list);
        if (empty($action_list['data'])){
            $this->setMessage('暂无数据!');
            return $action_list;
        }
        $action_ids = array_column($action_list['data'],'id');
        $action_results_list = OaProcessActionsResultRepository::getAllList(['action_id' => ['in',$action_ids]],['id','action_id','name']);
        foreach ($action_list['data'] as &$value){
            $value['results']       = [];
            if ($results = $this->searchArray($action_results_list,'action_id',$value['id'])){
                $value['results'] = $results;
            }
            $value['status_title']  = ProcessCommonStatusEnum::getLabelByValue($value['status']);
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $action_list;
    }

    /**
     * @desc节点动作添加负责人(老代码。已废弃)
     * @param integer $node_action_id   节点动作ID
     * @param integer $principal_ids    负责人ID
     * @param string $principal_iden    负责人身份标识【EXECUTOR:执行人，SUPERVISOR:监督人】
     * @return bool
     * @deprecated true
     */
    public function AddPrincipal($node_action_id, $principal_ids, $principal_iden)
    {
        if (!OaProcessNodeActionRepository::exists(['id' => $node_action_id])){
            $this->setError('节点动作不存在！');
            return false;
        }
        $principal_ids = explode(',',$principal_ids);
        $employee_list = OaEmployeeRepository::getAllList(['id' => ['in', $principal_ids]]);
        if (empty($employee_list) || count($principal_ids) != count($employee_list)){
            $this->setError('负责人不存在！');
            return false;
        }
        $add_arr = [];
        foreach ($principal_ids as $id){
            if (OaProcessActionPrincipalsRepository::exists(['node_action_id' => $node_action_id, 'principal_id' => $id])){
                $list_key = array_search($id, array_column($employee_list, 'id'));
                $name = empty($employee_list[$list_key]['real_name']) ? $employee_list[$list_key]['username'] : $employee_list[$list_key]['real_name'];
                $this->setError('负责人【'.$name.'】已添加，请勿重复添加！');
                return false;
            }
            if (OaProcessActionPrincipalsRepository::exists(['node_action_id' => $node_action_id, 'principal_iden' => ProcessPrincipalsEnum::getConst($principal_iden)])){
                $this->setError('只能添加一个'.ProcessPrincipalsEnum::$labels[$principal_iden].'！');
                return false;
            }
            $add_arr[] = [
                'node_action_id'    => $node_action_id,
                'principal_id'      => $id,
                'principal_iden'    => ProcessPrincipalsEnum::getConst($principal_iden),
                'created_at'        => time(),
                'updated_at'        => time(),
            ];
        }
        if (!OaProcessActionPrincipalsRepository::create($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }
}
            