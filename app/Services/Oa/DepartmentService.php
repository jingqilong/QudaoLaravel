<?php
namespace App\Services\Oa;


use App\Repositories\OaDepartmentRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DepartmentService extends BaseService
{
    use HelpTrait;

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
        if (empty($data['parent_id'])) $data['parent_id'] = 0;
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
    public function updateDepart(array $data)
    {
        if (!$departInfo = OaDepartmentRepository::getOne(['id' => $data['id']])){
            $this->setError('未查到该部门信息！');
            return false;
        }
        if (!$parentInfo = OaDepartmentRepository::getOne(['id' => $data['parent_id']])){
            $this->setError('父级部门不存在！');
            return false;
        }
        $upd_arr = [
            'name'        => $data['name'],
            'path'        => $parentInfo['path'].$data['id'].',',
            'level'       => $parentInfo['level'] + 1,
            'parent_id'   => $data['parent_id'],
            'updated_at'  => time(),
        ];
        if (!$id = OaDepartmentRepository::getUpdId(['id' => $data['id']],$upd_arr)){
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

        $this->removePagingField($depart_list);

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

    /**
     * 获取部门的联级列表
     * @return array
     */
    public function getDepartmentLinkageList(){
        $column = ['id','name','parent_id'];
        //先获取所有部门，联动列表用程序处理，减少数据库访问，部门过多不建议使用此方法
        if (!$department_list = OaDepartmentRepository::getAll($column)){
            $this->setMessage('暂无数据！');
            return [];
        }
        #父级部门ID，为0表示最顶级的部门
        $department_linkage_list = $this->getLayeredDepartmentList(0,$department_list);
        if (empty($department_linkage_list)){
            $this->setMessage('暂无数据！');
            return [];
        }
        $this->setMessage('获取成功！');
        return $department_linkage_list;
    }

    /**
     * 获取分层后的部门列表
     * @param int $parent_id                父级部门ID，为0表示最顶级的部门
     * @param array $all_department_list    所有的部门列表
     * @return array
     */
    public function getLayeredDepartmentList($parent_id, $all_department_list){
        if (empty($all_department_list)){
            return [];
        }
        $department_list = [];
        foreach ($all_department_list as $key => &$value){
            if ($parent_id == $value['parent_id']){
                unset($all_department_list[$key]);
//                $value['sub_departments'] = [];//前端不需要空数组
                if ($this->existsArray($all_department_list,'parent_id',$value['id'])){
                    $value['sub_departments'] = $this->getLayeredDepartmentList($value['id'],$all_department_list);
                }
                $department_list[] = $value;
            }
        }
        return $department_list;
    }
}
            