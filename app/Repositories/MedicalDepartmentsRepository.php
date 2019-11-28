<?php


namespace App\Repositories;


use App\Models\MedicalDepartmentsModel;
use App\Repositories\Traits\RepositoryTrait;

class MedicalDepartmentsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MedicalDepartmentsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取指定科室列表
     * @param array $department_ids
     * @param array $column
     * @return array|null
     */
    protected function getListDepartment(array $department_ids, $column=['*']){
        if (empty($department_ids)){
            return [];
        }
        $all_department_ids = [];
        foreach ($department_ids as $str){
            $str_arr = explode(',',$str);
            $all_department_ids = array_merge($all_department_ids,$str_arr);
        }
        $all_department_ids = array_unique($all_department_ids);
        $list = $this->getList(['id' => ['in',$all_department_ids]],$column);
        return $list;
    }
}
            