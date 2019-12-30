<?php


namespace App\Repositories;


use App\Models\OaDepartmentModel;
use App\Repositories\Traits\RepositoryTrait;

class OaDepartmentRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaDepartmentModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $info
     * @return mixed
     * @desc  添加部门
     */
    protected function addFirstDepartment(array $info){
        $department_data = [
            'parent_id' => $info['parent_id'],
            'name' => $info['name'],
            'level' =>  1,
            'created_at' => time(),
        ];
        if (!$id = $this->getAddId($department_data)){
            return false;
        }
        $res = $this->getUpdId(['id' => $id],['path' => $id,'parent_id' => 0]);
        return $res;
    }
    /**
     * @param array $info
     * @return mixed
     * @desc  添加部门
     */
    protected function addDepartment(array $info){
        $department_data = [
            'parent_id' => $info['parent_id'],
            'name' => $info['name'],
            'level' => $info['level'] + 1,
            'created_at' => time(),
        ];
        if (!$id = $this->getAddId($department_data)){
            return false;
        }
        $path = $id;
        if (!empty($info['path'])){
            $path = $info['path'].','.$id;
        }
        $res = $this->getUpdId(['id' => $id],['path' => $path]);
        return $res;
    }

    /**
     * @param array $info
     * @return mixed
     * @desc  更新部门
     */
    protected function updateDepartment(array $info){
        $department_data = [
            'parent_id' => $info['parent_id'],
            'name' => $info['name'],
            'level' => $info['level'],
            'updated_at' => time(),
        ];
        $res = $this->getUpdId(['id' => $info['id']],$department_data);
        return $res;
    }

    /**
     * 获取部门上下级路径
     * @param $department_id
     * @return array
     */
    protected function getDepartmentPath($department_id){
        if (empty($department_id)){
            return [];
        }
        if (!$department = $this->getOne(['id' => $department_id])){
            return [];
        }
        $path = trim($department['path'],',');
        $path = ltrim($path,'0,');
        $path = explode(',',$path);
        return $path;
    }
}
            