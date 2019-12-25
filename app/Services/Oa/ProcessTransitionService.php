<?php
namespace App\Services\Oa;

use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;

/**
 * @desc 基础数据：流程的节点流转操作  ModifiedBy bardo
 * Class ProcessTransitionService
 * @package App\Services\Oa
 */
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
     * 获取单个流转
     * @param $transition_id
     * @return bool|null
     */
    public function getTransition($transition_id){
        if (!$transition = OaProcessTransitionRepository::getOne(['id' => $request['transition_id']])){
            $this->setError('该流转不存在！');
            return false;
        }
        return $transition;
    }

    /**
     * 修改流转
     * @param $request
     * @return bool
     */
    public function editTransition($request)
    {
        if (!$transition= OaProcessTransitionRepository::getOne(['id' => $request['transition_id']])){
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
     * @desc 获取流转列表
     * @param array $where
     * @param int $page
     * @param int $pageNum
     * @return bool|null
     */
    public function getTransitionList($where,$page, $pageNum)
    {
        if (empty($where)){
            $where=['id' => ['>',0]];
        }
        if (!$transition_list = OaProcessTransitionRepository::getList($where,['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($transition_list['first_page_url'], $transition_list['from'],
            $transition_list['from'], $transition_list['last_page_url'],
            $transition_list['next_page_url'], $transition_list['path'],
            $transition_list['prev_page_url'], $transition_list['to']);
        if (empty($transition_list['data'])){
            $this->setMessage('暂无数据!');
            return $transition_list;
        }
        $this->setMessage('获取成功！');
        return $transition_list;
    }
}
            