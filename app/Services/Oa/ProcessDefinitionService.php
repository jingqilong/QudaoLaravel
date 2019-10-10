<?php
namespace App\Services\Oa;


use App\Enums\ProcessDefinitionEnum;
use App\Repositories\OaProcessCategoriesRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Services\BaseService;

class ProcessDefinitionService extends BaseService
{

    /**
     * 创建一个流程
     * @param $request
     * @return bool
     */
    public function createProcess($request)
    {
        if (!OaProcessCategoriesRepository::exists(['id' => $request['category_id']])){
            $this->setError('流程分类不存在！');
            return false;
        }
        if (OaProcessDefinitionRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'category_id'   => $request['category_id'],
            'description'   => $request['description'] ?? '',
            'status'        => ProcessDefinitionEnum::getConst($request['status']),
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessDefinitionRepository::getAddId($add_arr)){
            $this->setMessage('创建成功！');
            return true;
        }
        $this->setError('创建失败！');
        return false;
    }

    /**
     * 删除流程
     * @param $id
     * @return bool
     */
    public function deleteProcess($id)
    {
        if (!OaProcessDefinitionRepository::exists(['id' => $id])){
            $this->setError('该流程已被删除!');
            return false;
        }
        if (OaProcessDefinitionRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改流程定义
     * @param $request
     * @return bool
     */
    public function editProcess($request)
    {
        if (!$definition = OaProcessDefinitionRepository::getOne(['id' => $request['id']])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (!OaProcessCategoriesRepository::exists(['id' => $request['category_id']])){
            $this->setError('流程分类不存在！');
            return false;
        }
        if (OaProcessDefinitionRepository::exists(['name' => $request['name']]) && $definition['name'] != $request['name']){
            $this->setError('该名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'category_id'   => $request['category_id'],
            'description'   => $request['description'] ?? '',
            'status'        => ProcessDefinitionEnum::getConst($request['status']),
            'updated_at'    => time(),
        ];
        if (OaProcessDefinitionRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取流程列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getProcessList($page, $pageNum)
    {
        if (!$definition_list = OaProcessDefinitionRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($definition_list['first_page_url'], $definition_list['from'],
            $definition_list['from'], $definition_list['last_page_url'],
            $definition_list['next_page_url'], $definition_list['path'],
            $definition_list['prev_page_url'], $definition_list['to']);
        if (empty($definition_list['data'])){
            $this->setMessage('暂无数据!');
            return $definition_list;
        }
        foreach ($definition_list['data'] as &$value){
            $value['status'] = ProcessDefinitionEnum::getStatus($value['status']);
            $value['created_at'] = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $definition_list;
    }
}
            