<?php
namespace App\Services\Oa;


use App\Enums\ProcessDefinitionStatusEnum;
use App\Enums\ProcessGetwayTypeEnum;
use App\Repositories\OaProcessCategoriesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

/**
 * @desc 基础数据，流程分类操作
 * Class ProcessCategoriesService
 * @package App\Services\Oa
 */
class ProcessCategoriesService extends BaseService
{
    use HelpTrait;

    /**
     * 添加流程分类
     * @param $request
     * @return bool
     */
    public function addCategories($request)
    {
        $employee = Auth::guard('oa_api')->user();
        if (OaProcessCategoriesRepository::exists(['name' => $request['name']])){
            $this->setError('该名称已被使用！');
            return false;
        }
        $arr = [
            'name'          => $request['name'],
            'getway_type'   => $request['getway_type'] ?? 0,
            'getway_name'   => $request['getway_name'] ?? '',
            'status'        => $request['status'],
            'created_at'    => time(),
            'created_by'    => $employee->id,
            'updated_at'    => time(),
            'updated_by'    => $employee->id,
        ];
        if (OaProcessCategoriesRepository::getAddId($arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除流程分类
     * @param $id
     * @return bool
     */
    public function deleteCategories($id)
    {
        if (!OaProcessCategoriesRepository::exists(['id' => $id])){
            $this->setError('分类已被删除！');
            return false;
        }
        if (OaProcessCategoriesRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改流程分类
     * @param $request
     * @return bool
     */
    public function editCategories($request)
    {
        $employee = Auth::guard('oa_api')->user();
        if (!$cate = OaProcessCategoriesRepository::getOne(['id' => $request['id']])){
            $this->setError('该分类不存在！');
            return false;
        }
        if (OaProcessCategoriesRepository::exists(['name' => $request['name']]) && $cate['name'] != $request['name']){
            $this->setError('该名称已被使用！');
            return false;
        }
        $arr = [
            'name'          => $request['name'],
            'getway_type'   => $request['getway_type'] ?? 0,
            'getway_name'   => $request['getway_name'] ?? '',
            'status'        => $request['status'],
            'updated_at'    => time(),
            'updated_by'    => $employee->id,
        ];
        if (OaProcessCategoriesRepository::getUpdId(['id' => $request['id']],$arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取分类列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getCategoriesList($page, $pageNum)
    {
        if (!$cate_list = OaProcessCategoriesRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        $cate_list = $this->removePagingField($cate_list);
        if (empty($cate_list['data'])){
            $this->setMessage('暂无数据!');
            return $cate_list;
        }
        $cate_list['data'] = EmployeeService::getListOperationByName($cate_list['data']);
        foreach ($cate_list['data'] as &$value){
            $value['getway_type_title']     = ProcessGetwayTypeEnum::getGetWayType($value['getway_type']);
            $value['status_title']          = ProcessDefinitionStatusEnum::getLabelByValue($value['status']);
            $value['created_at']            = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']            = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $cate_list;
    }
}
            