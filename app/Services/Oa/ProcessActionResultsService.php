<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Services\BaseService;

/**
 * @desc 基础数据定义 流程动作结果操作。 modifiedBy: Bardo
 * Class ProcessActionResultsService
 * @package App\Services\Oa
 */
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
            $this->setError('无法删除已使用的动作结果！');
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
     * 获取动作结果
     * @param $result_id
     * @return mixed
     */
    public function getActionResult($result_id)
    {
        if (!$action_result=OaProcessActionsResultRepository::getOne(['id' => $result_id])){
            $this->setError('该动作结果不存在！');
            return false;
        }
        return $action_result;
    }

    /**
     * 获取动作结果
     * @param $result_id
     * @return mixed
     */
    public function getActionResultText($result_id)
    {
        if (!$action_result=OaProcessActionsResultRepository::getOne(['id' => $result_id])){
            $this->setError('该动作结果不存在！');
            return false;
        }
        return $action_result['name'];
    }

    /**
     * 修改动作
     * @param $request
     * @return bool
     */
    public function editActionResult($request)
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
     * @desc 获取动作结果列表
     * @param $where
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getActionResultList($where, $page, $pageNum)
    {
        if (empty($where)){
            $where=['id' => ['>',0]];
        }
        if (!$action_list = OaProcessActionsResultRepository::getList($where,['*'],'id','asc',$page,$pageNum)){
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
            