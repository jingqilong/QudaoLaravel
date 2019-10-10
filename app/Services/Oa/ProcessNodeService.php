<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Services\BaseService;

class ProcessNodeService extends BaseService
{

    /**
     * 给流程添加节点
     * @param $request
     * @return bool
     */
    public function processAddNode($request)
    {
        if (!OaProcessDefinitionRepository::exists(['id' => $request['process_id']])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (OaProcessNodeRepository::exists(['name' => $request['name'], 'process_id' => $request['process_id']])){
            $this->setError('节点名称已被占用！');
            return false;
        }
        $add_arr = [
            'process_id'    => $request['process_id'],
            'name'          => $request['name'],
            'limit_time'    => $request['limit_time'] ?? 0,
            'icon'          => $request['icon'] ?? '',
            'position'      => $request['position'],
            'description'   => $request['description'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessNodeRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            