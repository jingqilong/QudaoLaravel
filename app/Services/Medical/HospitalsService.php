<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Repositories\CommonAreaRepository;
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
        $area_codes = explode(',',$request['area_code']);
        if (count($area_codes) != CommonAreaRepository::count(['code' => ['in',$area_codes]])){
            $this->setError('无效的地区代码！');
            return false;
        }
        if (MediclaHospitalsRepository::exists(['name' => $request['name']])){
            $this->setError('医院名称已被使用！');
            return false;
        }
        if (!DoctorEnum::getCategory($request['category'])){
            $this->setError('医院类别不存在!');
            return false;
        }
        $add_arr = [
            'name'             => $request['name'],
            'category'         => $request['category'],
            'img_ids'          => $request['img_ids'],
            'department_ids'   => $request['department_ids'],
            'introduction'     => $request['introduction'],
            'area_code'        => $request['area_code'] . ',',
            'longitude'        => $request['log'],
            'latitude'         => $request['lat'],
            'address'          => $request['address'],
            'recommend'        => $request['recommend'] == 1 ? time() : 0,
            'created_at'       => time(),
            'updated_at'       => time()
        ];
        if (MediclaHospitalsRepository::exists($add_arr)){
            $this->setMessage('数据已存在！');
            return false;
        }
        $add_arr['created_at'] =  time();
        $add_arr['updated_at'] =  time();
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
        $upd_arr = [
            'name'             => $request['name'],
            'category'         => $request['category'],
            'img_ids'          => $request['img_ids'],
            'department_ids'   => $request['department_ids'],
            'introduction'     => $request['introduction'],
            'area_code'        => $request['area_code'] . ',',
            'address'          => $request['address'],
            'recommend'        => $request['recommend'] == 1 ? time() : 0,
        ];
        if (MediclaHospitalsRepository::exists(array_merge(['id' => ['<>',$request['id'],'deleted_at' => 0]],$upd_arr))){
            $this->setError('医院已存在！');
            return false;
        }
        $upd_arr['updated_at'] = time();
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
        if (!$list = MediclaHospitalsRepository::getList(['deleted_at' => 0],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']    = ImagesService::getListImagesConcise($list['data'],['img_ids' => 'several']);
        $department_ids  = array_column($list['data'],'department_ids');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        foreach ($list['data'] as &$value){
            $value['departments']    = [];
            $value['img_urls']       = [];
            $department_arr = explode(',',$value['department_ids']);
            foreach ($department_arr as $item){
                if ($department = $this->searchArray($department_list,'id',$item)){
                    $value['departments'][] = reset($department);
                }
            }
            #处理地址
            list($area_address,$lng,$lat) = $this->makeAddress($value['area_code'],$value['address']);
            $value['area_address']  = $area_address;
            $value['area_code']     = rtrim($value['area_code'],',');
            $value['lng']           = $lng;
            $value['lat']           = $lat;
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
        $keywords   = $request['keywords'] ?? null;
        $column     = ['id','name','recommend','introduction','department_ids','area_code','address','img_ids'];
        $where      = ['deleted_at' => 0];
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','address']];
            if (!$list = MediclaHospitalsRepository::search($keyword,$where,$column,$page,$page_num,'recommend','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = MediclaHospitalsRepository::getList($where,$column,'recommend','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']    = ImagesService::getListImagesConcise($list['data'],['img_ids' => 'single']);
        $department_ids  = array_column($list['data'],'department_ids');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        foreach ($list['data'] as &$value){
            $department_arr = explode(',',$value['department_ids']);
            foreach ($department_arr as $item){
                if ($department = $this->searchArray($department_list,'id',$item)){
                    $value['departments'][] = reset($department);
                }
            }
            $value['recommend']  = $value['recommend'] == 0 ? 0 : 1;
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],$value['address']);
            $value['area_address']  = $area_address;
            $value['area_code']     = rtrim($value['area_code'],',');
            unset($value['img_ids']);
        }
        unset($list['introduction']);
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取医院详情
     * @param $request
     * @return array|bool|null
     */
    public function getHospitalDetail($request)
    {
        if(!$hospital = MediclaHospitalsRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('医院不存在!');
            return false;
        }
        $hospital = ImagesService::getOneImagesConcise($hospital,['img_ids' => 'several']);
        $department = explode(',',$hospital['department_ids']);
        $hospital['recommend']  = $hospital['recommend'] == 0 ? 0 : 1;
        #处理地址
        list($area_address) = $this->makeAddress($hospital['area_code'],$hospital['address']);
        $hospital['area_address']  = $area_address;
        $hospital['area_code']     = rtrim($hospital['area_code'],',');
        $hospital['department_name'] = MedicalDepartmentsRepository::getList(['id' => ['in',$department]],['id','name']);
        unset($hospital['created_at'],$hospital['updated_at'],$hospital['deleted_at'],$hospital['department_ids']);
        $this->setMessage('获取成功!');
        return $hospital;

    }
}
            