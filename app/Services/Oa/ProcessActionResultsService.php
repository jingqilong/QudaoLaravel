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
}
            