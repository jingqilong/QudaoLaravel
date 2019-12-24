<?php
namespace App\Services\Oa;

use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;
use App\Repositories\OaProcessNodeActionsResultRepository;

class ProcessNodeActionResultService extends BaseService
{
    /**
     * 添加节点动作结果
     * @param $request
     * @return bool
     */
    public function addNodeActionResult($request)
    {
        $add_arr = [
            'node_action_id'          => $request['node_action_id'],
            'action_result_id'       => $request['action_result_id'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessNodeActionsResultRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除节点动作结果
     * @param $node_action_result_id
     * @return bool
     */
    public function deleteAction($node_action_result_id)
    {
        if (!OaProcessNodeActionsResultRepository::exists(['id' => $node_action_result_id])){
            $this->setError('该动作不存在！');
            return false;
        }
        if (!OaProcessNodeActionsResultRepository::delete(['id' => $node_action_result_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改节点动作结果
     * @param $request
     * @return bool
     */
    public function editAction($request)
    {
        if (!$action = OaProcessNodeActionsResultRepository::getOne(['id' => $request['node_action_result_id']])){
            $this->setError('该事件不存在！');
            return false;
        }

        $upd_arr = [
            'node_action_id'          => $request['node_action_id'],
            'action_result_id'       => $request['action_result_id'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessNodeActionsResultRepository::getUpdId(['id' => $request['node_action_result_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取节点动作结果列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getActionList($page, $pageNum)
    {
        if (!$action_list = OaProcessNodeActionsResultRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
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