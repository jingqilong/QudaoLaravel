<?php
namespace App\Services\Oa;


use App\Enums\ProcessCategoryEnum;
use App\Repositories\OaProcessCategoriesRepository;
use App\Services\BaseService;

class ProcessCategoriesService extends BaseService
{

    /**
     * 添加流程分类
     * @param $request
     * @return bool
     */
    public function addCategories($request)
    {
        if (OaProcessCategoriesRepository::exists(['name' => $request['name']])){
            $this->setError('该分类已存在！');
            return false;
        }
        $arr = [
            'name'          => $request['name'],
            'getway_type'   => isset($request['getway_type']) ? ProcessCategoryEnum::getConst($request['getway_type']) : 0,
            'getway_name'   => $request['getway_name'] ?? '',
            'status'        => ProcessCategoryEnum::getConst($request['status']),
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessCategoriesRepository::getAddId($arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            