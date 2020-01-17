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
            $this->setError('医生标签不存在或已删除！');
            return false;
        }
        if ($list = MedicalDoctorsRepository::getList(['label_ids' => ['like','%,'.$id.',']],['id','name'])){
            $this->setMessage('该医生标签已使用，无法删除！');
            return $list;
        }
        if (MedicalDoctorLabelsRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
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
        if (!$list = MedicalDoctorLabelsRepository::getList(['id' => ['>',0]],['*'],'id','asc')){
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
            