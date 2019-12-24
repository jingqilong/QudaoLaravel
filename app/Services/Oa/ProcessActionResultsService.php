<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Services\BaseService;

class ProcessActionResultsService extends BaseService
{
    /**
     * 添加动作结果
     * @param $request
     * @return bool
     */
    public function addActionResult($request)
    {
        if (OaProcessActionsResultRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'action_id'       => $request['action_id'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionsResultRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除动作结查
     * @param $result_id
     * @return bool
     */
    public function deleteActionResult($result_id)
    {
        if (!OaProcessActionsResultRepository::exists(['id' => $result_id])){
            $this->setError('该动作结果不存在！');
            return false;
        }
        if (OaProcessNodeActionsResultRepository::exists(['action_result_id' => $result_id])){
            $this->setError('无法删除正在使用的动作结果！');
            return false;
        }
        if (!OaProcessActionsResultRepository::delete(['id' => $result_id])){
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
        if (!$action = OaProcessActionsResultRepository::getOne(['id' => $request['result_id']])){
            $this->setError('该事件不存在！');
            return false;
        }
        if ($action['name'] != $request['name'] && OaProcessActionsResultRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'action_id'       => $request['action_id'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessActionsResultRepository::getUpdId(['id' => $request['result_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取动作列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getActionList($page, $pageNum)
    {
        if (!$action_list = OaProcessActionsResultRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
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
        $this->setMessage('获取成功！');
        return $action_list;
    }

//    /**
//     * 节点动作添加负责人
//     * @param integer $node_action_id   节点动作ID
//     * @param integer $principal_ids    负责人ID
//     * @param string $principal_iden    负责人身份标识【EXECUTOR:执行人，SUPERVISOR:监督人】
//     * @return bool
//     */
//    public function AddPrincipal($node_action_id, $principal_ids, $principal_iden)
//    {
//        if (!OaProcessNodeActionRepository::exists(['id' => $node_action_id])){
//            $this->setError('节点动作不存在！');
//            return false;
//        }
//        $principal_ids = explode(',',$principal_ids);
//        $employee_list = OaEmployeeRepository::getList(['id' => ['in', $principal_ids]]);
//        if (empty($employee_list) || count($principal_ids) != count($employee_list)){
//            $this->setError('负责人不存在！');
//            return false;
//        }
//        $add_arr = [];
//        foreach ($principal_ids as $id){
//            if (OaProcessActionPrincipalsRepository::exists(['node_action_id' => $node_action_id, 'principal_id' => $id])){
//                $list_key = array_search($id, array_column($employee_list, 'id'));
//                $name = empty($employee_list[$list_key]['real_name']) ? $employee_list[$list_key]['username'] : $employee_list[$list_key]['real_name'];
//                $this->setError('负责人【'.$name.'】已添加，请勿重复添加！');
//                return false;
//            }
//            if (OaProcessActionPrincipalsRepository::exists(['node_action_id' => $node_action_id, 'principal_iden' => ProcessPrincipalsEnum::getConst($principal_iden)])){
//                $this->setError('只能添加一个'.ProcessPrincipalsEnum::$labels[$principal_iden].'！');
//                return false;
//            }
//            $add_arr[] = [
//                'node_action_id'    => $node_action_id,
//                'principal_id'      => $id,
//                'principal_iden'    => ProcessPrincipalsEnum::getConst($principal_iden),
//                'created_at'        => time(),
//                'updated_at'        => time(),
//            ];
//        }
//        if (!OaProcessActionPrincipalsRepository::create($add_arr)){
//            $this->setError('添加失败！');
//            return false;
//        }
//        $this->setMessage('添加成功！');
//        return true;
//    }
}
            