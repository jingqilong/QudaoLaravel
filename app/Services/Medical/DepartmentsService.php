<?php
namespace App\Services\Medical;


use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

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
            'describe'   => $request['describe'] ?? '',
            'icon'       => $request['icon'],
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
     * @return mixed
     */
    public function deleteDepartments($id)
    {
        if (!MedicalDepartmentsRepository::exists(['id' => $id])){
            $this->setError('医疗科室已删除或不存在！');
            return false;
        }
        if ($list = MedicalDoctorsRepository::getAllList(['deleted_at' => 0,'department_ids' => ['like','%,' . $id . ',%']],['id','name'])){
            $this->setMessage('该医疗科室已被医生使用，无法删除！');
            return $list;
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
            $this->setError('科室信息不存在！');
            return false;
        }
        $upd_arr = [
            'name'       => $request['name'],
            'describe'   => $request['describe'] ?? '',
            'icon'       => $request['icon'],
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
     * 获取医疗科室列表 OA
     * @param $request
     * @return mixed
     */
    public function departmentsList($request)
    {
        $column     = ['id','name','describe','icon','created_at'];
        $where      = ['id' => ['>',0]];
        if (!$list = MedicalDepartmentsRepository::getList($where,$column,'id','asc')){
            $this->setError('获取失败！');
            return false;
        }
        $list         = $this->removePagingField($list);
        $list['data'] = ImagesService::getListImagesConcise($list['data'],['icon' => 'single']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value) $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
        $this->setMessage('获取成功！');
        return $list;
    }


    /**
     * 用户 获取医疗科室列表
     * @param $request
     * @return mixed
     */
    public function getDepartmentsList($request)
    {
        $keywords   = $request['keywords'] ?? null;
        $column     = ['id','name','describe','icon'];
        $where      = ['id' => ['>',0]];
        if (!empty($keywords)){
            $keyword = [$keywords => ['name']];
            if (!$list = MedicalDepartmentsRepository::search($keyword,$where,$column,'id','asc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = MedicalDepartmentsRepository::getList($where,$column,'id','asc')){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list         = $this->removePagingField($list);
        $list['data'] = ImagesService::getListImagesConcise($list['data'],['icon' => 'single']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            