<?php
namespace App\Services\Medical;


use App\Repositories\CommonImagesRepository;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class HospitalsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加医院
     * @param $request
     * @return bool
     */
    public function addHospitals($request)
    {
        $add_arr = [
            'name'          => $request['name'],
        ];
        if (MediclaHospitalsRepository::exists(['name' => $add_arr['name']])){
            $this->setError('医院名称已被使用！');
            return false;
        }
        $add_arr['introduction']   =$request['introduction'];
        $add_arr['recommend']   = $request['recommend'] == 1 ? time() : 0;
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (MediclaHospitalsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除医院
     * @param $id
     * @return bool
     */
    public function deleteHospitals($id)
    {
        if (!MediclaHospitalsRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('医院已删除！');
            return false;
        }
        if (MedicalDoctorsRepository::exists(['id' => $id])){
            $this->setError('该医院下有医生，无法删除！');
            return false;
        }
        if (MediclaHospitalsRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改医院
     * @param $request
     * @return bool
     */
    public function editHospitals($request)
    {
        if (!MediclaHospitalsRepository::exists(['id' => $request['id']])){
            $this->setError('医院信息不存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['name' => $request['name'],'id' => ['<>',$request['id']]])){
            $this->setError('医院信息不存在！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'introduction'  => $request['introduction'],
            'recommend'     => $request['recommend'] == 1 ? time() : 0,
            'updated_at'    => time(),
        ];

        if (MediclaHospitalsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取医院列表
     * @param $request
     * @return bool
     */
    public function getHospitalsList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = MediclaHospitalsRepository::getList(['deleted_at' =>0],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']    = ImagesService::getListImages($list['data'],['img_ids' => 'single']);
        foreach ($list['data'] as &$value){
            $value['recommend']  = $value['recommend'] == 0 ? 0 : 1;
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 用户获取医院列表
     * @param $request
     * @return bool
     */
    public function getHospitalList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $column     = ['id','name','recommend','introduction','img_ids'];
        if (!$list = MediclaHospitalsRepository::getList(['deleted_at' =>0],$column,'recommend','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']    = ImagesService::getListImages($list['data'],['img_ids' => 'single']);
        foreach ($list['data'] as &$value){
            $value['recommend']  = $value['recommend'] == 0 ? 0 : 1;
            unset($value['img_ids']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            