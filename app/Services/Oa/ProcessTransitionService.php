<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;

class ProcessTransitionService extends BaseService
{
    /**
     * 添加流转
     * @param $request
     * @return bool
     */
    public function addTransition($request)
    {
        if (OaProcessTransitionRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'process_id'   => $request['process_id'],
            'node_action_result_id'  => $request['node_action_result_id'],
            'current_node'    => $request['current_node'],
            'next_node' => $request['next_node'],
            'status'  => $request['status'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessTransitionRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除流转
     * @param $transition_id
     * @return bool
     */
    public function deleteTransition($transition_id)
    {
        if (!OaProcessTransitionRepository::exists(['id' => $transition_id])){
            $this->setError('该流转不存在！');
            return false;
        }
        if (!OaProcessTransitionRepository::delete(['id' => $transition_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改流转
     * @param $request
     * @return bool
     */
    public function editTransition($request)
    {
        if (!$action = OaProcessTransitionRepository::getOne(['id' => $request['transition_id']])){
            $this->setError('该流转不存在！');
            return false;
        }
        $upd_arr = [
            'process_id'   => $request['process_id'],
            'node_action_result_id'  => $request['node_action_result_id'],
            'current_node'    => $request['current_node'],
            'next_node' => $request['next_node'],
            'status'  => $request['status'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessTransitionRepository::getUpdId(['id' => $request['transition_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取流转列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getTransitionList($page, $pageNum)
    {
        if (!$action_list = OaProcessTransitionRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
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
}
            