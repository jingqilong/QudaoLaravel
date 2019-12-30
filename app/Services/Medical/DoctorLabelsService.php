<?php
namespace App\Services\Medical;


use App\Repositories\MedicalDoctorLabelsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class DoctorLabelsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加医生标签
     * @param $request
     * @return bool
     */
    public function addDoctorLabels($request)
    {
        $add_arr = [
            'name'     => $request['name'],
        ];
        if (MedicalDoctorLabelsRepository::exists($add_arr)){
            $this->setError('医生标签已存在！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (MedicalDoctorLabelsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除医生标签
     * @param $id
     * @return bool
     */
    public function deleteDoctorLabels($id)
    {
        if (!MedicalDoctorLabelsRepository::exists(['id' => $id])){
            $this->setError('标签已删除！');
            return false;
        }
        if (MedicalDoctorsRepository::exists(['label_ids' => ['like','%'.$id.',']])){
            $this->setError('该标签正在使用，无法删除！');
            return false;
        }
        if (!MedicalDoctorLabelsRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }


    /**
     * 修改医生标签
     * @param $request
     * @return bool
     */
    public function editDoctorLabels($request)
    {
        if (!MedicalDoctorLabelsRepository::exists(['id' => $request['id']])){
            $this->setError('医生标签信息不存在！');
            return false;
        }
        if (MedicalDoctorLabelsRepository::exists(['id' => ['<>',$request['id']],'name' => $request['name']])){
            $this->setError('医生标签已存在！');
            return false;
        }
        $upd_arr = [
            'name'     => $request['name'],
            'updated_at' => time(),
        ];
        if (MedicalDoctorLabelsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取医生标签列表
     * @param $request
     * @return bool
     */
    public function getDoctorLabelsList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = MedicalDoctorLabelsRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            