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
}
            