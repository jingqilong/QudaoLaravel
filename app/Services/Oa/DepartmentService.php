<?php
namespace App\Services\Oa;


use App\Repositories\OaDepartmentRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

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
     * @return mixed
     * @desc 添加新部门
     */
    public function addDepart(array $data)
    {
        $path       = '0,';
        $parent_id  = 0;
        $level      = 0;
        if (isset($data['parent_id']) && (0 != $data['parent_id'])){
            if (!$parent_info = OaDepartmentRepository::getOne(['id' => $data['parent_id']])){
                $this->setError('父级部门不存在！');
                return false;
            }
            $path       = $parent_info['path'];
            $parent_id  = $parent_info['id'];
            $level      = $parent_info['level'];
        }

        if (OaDepartmentRepository::exists(['name' => $data['name'],'level' => $level + 1,'path' => ['like',$path.'%']])){
            $this->setError('部门名称已存在！');
            return false;
        }
        $add_arr = [
            'name'        => $data['name'],
            'parent_id'   => $parent_id,
            'level'       => $level + 1,
            'created_at'  => time(),
            'updated_at'  => time(),
        ];
        DB::beginTransaction();
        if (!$id = OaDepartmentRepository::getAddId($add_arr)){
            $this->setError('添加部门失败！');
            DB::rollBack();
            return false;
        }
        $upd_arr = [
            'path' => $path. $id . ',',
        ];
        if (!$id = OaDepartmentRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('添加部门失败！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('添加部门成功!');
        return true;
    }

    /**
     * @param array $data
     * @return mixed
     * @desc 修改部门
     */
    /*public function updateDepart(array $data)
    {
        if ($depart = OaDepartmentRepository::getOne(['parent_id' => $data['parent_id'],'path' => $data['path'],'level' => $data['level']]))
        {
            if (!$res = OaDepartmentRepository::updateDepartment($data)){
                return ['code' => 0, 'message' => '修改失败'];
            }
            return ['code' => 1, 'message' => '修改成功'];
        }
        return ['code' => 0, 'message' => '查询不到该部门'];

    }*/
    public function updateDepart(array $data)
    {
        if (!$departInfo = OaDepartmentRepository::getOne(['id' => $data['id']])){
            $this->setError('未查到该部门信息！请重试');
            return false;
        }
        $data['updated_at'] = time();
        if (!$id = OaDepartmentRepository::getUpdId(['id' => $data['id']],['name' => $data['name'],'updated_at' => $data['updated_at']])){
            $this->setError('修改部门信息失败！');
            return false;
        }
        $this->setMessage('修改部门信息成功！');
        return true;
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
            $path_data['path']    = explode(',',$value['path']);
            $value['parent']      = OaDepartmentRepository::getField(['id' => $value['parent_id']],'name');
            $depart_name          = OaDepartmentRepository::getList(['id' => ['in',$path_data['path']]],['name']);
            $value['path']        = array_column($depart_name,'name');
            $value['created_at']  = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']  = date('Y-m-d H:m:s',$value['updated_at']);
        }

        $this->setMessage('获取成功！');
        return $depart_list;
    }
}
            