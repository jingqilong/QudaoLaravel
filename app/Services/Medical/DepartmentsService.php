<?php
namespace App\Services\Medical;


use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use function Sodium\add;

class DepartmentsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加医疗科室
     * @param $request
     * @return bool
     */
    public function addDepartments($request)
    {
        $add_arr = [
            'name'       => $request['name'],
            'describe'   => $request['describe'],
            'created_at' => time(),
            'updated_at' => time(),
        ];
        if (MedicalDepartmentsRepository::exists(['name' => $add_arr['name']])){
            $this->setError('医疗科室已存在！');
            return false;
        }

        if (MedicalDepartmentsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除医疗科室
     * @param $id
     * @return bool
     */
    public function deleteDepartments($id)
    {
        if (!MedicalDepartmentsRepository::exists(['id' => $id])){
            $this->setError('医疗科室已删除！');
            return false;
        }
        if (MedicalDoctorsRepository::exists(['department_ids' => $id])){
            $this->setError('该医疗科室正在使用，无法删除！');
            return false;
        }
        if (MedicalDepartmentsRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改医疗科室
     * @param $request
     * @return bool
     */
    public function editDepartments($request)
    {
        if (!MedicalDepartmentsRepository::exists(['id' => $request['id']])){
            $this->setError('医疗科室信息不存在！');
            return false;
        }
        $upd_arr = [
            'name'       => $request['name'],
            'describe'   => $request['describe'],
            'updated_at' => time(),
        ];
        if (MedicalDepartmentsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取医疗科室列表
     * @param $request
     * @return bool
     */
    public function getDepartmentsList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = MedicalDepartmentsRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            