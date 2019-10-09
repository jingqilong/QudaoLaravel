<?php
namespace App\Services\Oa;


use App\Repositories\OaDepartmentRepository;
use App\Services\BaseService;

class DepartmentService extends BaseService
{

    /**
     * @return array|null
     * @return array
     * @desc 获取部门
     */
    public function getDepart()
    {
        if (!$depart = OaDepartmentRepository::getAll()){
            return ['code' => 0, 'message' => '没有部门存在，请添加部门！'];
        }
        return $depart;
    }


    /**
     * @param array $data
     * @return array
     * @desc 添加新部门
     */
    public function addDepart(array $data)
    {
        if (!$parent_info = OaDepartmentRepository::getOne(['id' => $data['parent_id']])){
            return ['code' => 0, 'message' => '父级部门不存在'];
        }
        if ($depart = OaDepartmentRepository::getOne(['name' => $data['name'], 'parent_id' => $data['parent_id'],'level' => $parent_info['level'] + 1])){
            return ['code' => 0, 'message' => '部门信息已存在'];
        }
        $data['path'] = $parent_info['path'];
        $data['level'] = $parent_info['level'];
        if (!$res = OaDepartmentRepository::addDepartment($data)){
            return ['code' => 0, 'message' => '添加失败'];
        }
        return ['code' => 1, 'message' => '添加成功'];
    }

    /**
     * @param array $data
     * @return array
     * @desc 修改部门
     */
    public function updateDepart(array $data)
    {
        if ($depart = OaDepartmentRepository::getOne(['parent_id' => $data['parent_id'],'path' => $data['path'],'level' => $data['level']]))
        {
            if (!$res = OaDepartmentRepository::updateDepartment($data)){
                return ['code' => 0, 'message' => '修改失败'];
            }
            return ['code' => 1, 'message' => '修改成功'];
        }
        return ['code' => 0, 'message' => '查询不到该部门'];

    }

    /**
     * @param string $id
     * @return array
     * @desc  删除部门
     */
    public function delDepart(string $id){
        if (!$id = OaDepartmentRepository::getOne(['id' => $id])){
            return ['code' => 0, 'message' => '信息错误,没有此部门'];
        }
        if ($res = OaDepartmentRepository::delete(['id' => $id])){
            return ['code' => 1, 'message' => '删除成功'];
        }
        return ['code' => 0, 'message' => '删除失败'];
    }

    /**
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getDepartList($page,$pageNum)
    {
        if (!$depart_list = OaDepartmentRepository::getList(['id' => ['>',0]],['field' => '*'],'','',$page,$pageNum)){
                $this->setError('获取失败!');
                return false;
        }
        
        unset($depart_list['first_page_url'], $depart_list['from'],
            $depart_list['from'], $depart_list['last_page_url'],
            $depart_list['next_page_url'], $depart_list['path'],
            $depart_list['prev_page_url'], $depart_list['to']);
        if (empty($depart_list['data'])){
            $this->setMessage('暂无数据!');
            return $depart_list;
        }

        $path_data = [];
        foreach ($depart_list['data'] as &$value)
        {
            $path_data['path']   = explode(',',$value['path']);
            $value['parent']     = OaDepartmentRepository::getField(['id' => $value['parent_id']],'name');
            $depart_name         = OaDepartmentRepository::getList(['id' => ['in',$path_data['path']]],['name']);
            $value['path']       = array_column($depart_name,'name');
            $value['created_at'] = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:s',$value['updated_at']);
        }

        $this->setMessage('获取成功！');
        return $depart_list;
    }
}
            