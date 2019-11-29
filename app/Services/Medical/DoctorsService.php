<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorLabelsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class DoctorsService extends BaseService
{
    use HelpTrait;


    public function addDoctors($request)
    {
        if(isset($request['member_id'])){
            if (!MemberRepository::exists(['m_id' => $request['member_id']])){
                $this->setError('该会员不存在');
                return false;
            }
        }
        $label_ids = explode(',',$request['department_ids']);
        if (count($label_ids) != MedicalDepartmentsRepository::exists(['id' => ['in',$label_ids]])){
            $this->setError('科室不存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospitals_id']])){
            $this->setError('医院不存在！');
            return false;
        }
        $add_arr = [
            'name'              => $request['name'],
            'title'             => $request['title'],
            'sex'               => $request['sex'],
            'img_id'            => $request['img_id'],
            'good_at'           => $request['good_at'],
            'introduction'      => $request['introduction'],
            'label_ids'         => isset($request['label_ids']) ? $request['label_ids'].',' : '',
            'hospitals_id'      => $request['hospitals_id'],
            'member_id'         => $request['member_id'] ?? 0,
            'department_ids'    => isset($request['department_ids']) ? ',' . $request['department_ids'] . ',' : '',
        ];
        if (MedicalDoctorsRepository::exists($add_arr)){
            $this->setError('医生已存在！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        $add_arr['recommend']  = $request['recommend'] == 1 ? time() : 0;
        if (MedicalDoctorsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除医生信息
     * @param $id
     * @return bool
     */
    public function deleteDoctors($id)
    {
        if (!MedicalDoctorsRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('医生信息已删除！');
            return false;
        }
        if (MedicalDoctorsRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改醫生信息
     * @param $request
     * @return bool
     */
    public function editDoctors($request)
    {
        $label_ids = explode(',',$request['department_ids']);
        if (count($label_ids) != MedicalDepartmentsRepository::exists(['id' => ['in',$label_ids]])){
            $this->setError('科室不存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospitals_id']])){
            $this->setError('医院不存在！');
            return false;
        }
        $upd_arr = [
            'name'              => $request['name'],
            'title'             => $request['title'],
            'img_id'            => $request['img_id'],
            'sex'               => $request['sex'],
            'good_at'           => $request['good_at'],
            'introduction'      => $request['introduction'],
            'hospitals_id'      => $request['hospitals_id'],
            'recommend'         => $request['recommend'],
            'label_ids'         => isset($request['label_ids']) ? $request['label_ids'].',' : '',
            'department_ids'    => isset($request['department_ids']) ? ',' . $request['department_ids'] . ',' : '',
        ];
        $upd_arr['updated_at']  = time();
        if (MedicalDoctorsRepository::exists($upd_arr)){
            $this->setError('医生已存在！');
            return false;
        }
        if (MedicalDoctorsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取医生列表（OA）
     * @param $request
     * @return bool|mixed|null
     */
    public function getDoctorsListPage($request)
    {
        if (empty($request['asc'])){
            $request['asc'] = 1;
        }
        $page           = $request['page'] ?? 1;
        $asc            = $request['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $request['page_num'] ?? 20;
        $keywords       = $request['keywords'] ?? null;
        $where          = ['deleted_at' => 0];
        if (!empty($keywords)){
            $keyword        = [$keywords => ['name','sex','department_ids']];
            if (!$list = MedicalDoctorsRepository::search($keyword,$where,['*'],$page,$page_num,'created_at',$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = MedicalDoctorsRepository::getList($where,['*'],'created_at',$asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }

        $list['data']    = ImagesService::getListImages($list['data'],['img_id' => 'single']);
        $department_ids  = array_column($list['data'],'department_ids');
        $hospitals_ids   = array_column($list['data'],'hospitals_id');
        $labels_ids      = array_column($list['data'],'label_ids');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);
        $labels_list     = MedicalDoctorLabelsRepository::getAssignList($labels_ids,['id','name']);

        foreach ($list['data'] as &$value){
            $value['departments']    = [];
            $value['hospitals_name'] = '';
            $value['labels']         = [];
            $department_arr = explode(',',$value['department_ids']);
            foreach ($department_arr as $item){
                if ($department = $this->searchArray($department_list,'id',$item)){
                    $value['departments'][] = reset($department);
                }
            }
            $labels_arr = explode(',',$value['label_ids']);
            foreach ($labels_arr as $item){
                if ($label = $this->searchArray($labels_list,'id',$item)){
                    $value['labels'][] = reset($label);
                }
            }
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospitals_id'])){
                $value['hospitals_name'] = reset($hospitals)['name'];
            }
            $value['recommend']         = $value['recommend'] == 0 ? 0 : 1;
            $value['department_ids']    = trim($value['department_ids'],',');
            $value['label_ids']         = trim($value['label_ids'],',');
        }

        $this->setMessage('获取成功!');
        return $list;
    }


    /**
     *  用户获取医生详情
     * @param $request
     * @return array|bool|null
     */
    public function getDoctorById($request)
    {
        if (!MedicalDoctorsRepository::exists(['id' => $request['id']])){
            $this->setError('医生不存在!');
            return false;
        }
        if (!$doctorInfo = MedicalDoctorsRepository::getOne(['id' => $request['id']])){
            $this->setError('获取失败!');
            return false;
        }
        if (!$hospital = MediclaHospitalsRepository::getOne(['id' => $doctorInfo['hospitals_id']])){
            $this->setError('医院不存在!');
            return false;
        }
        if (empty($doctorInfo)){
            $this->setError('获取失败!');
            return false;
        }
        $doctorInfo['departments']      = [];
        $doctorInfo['labels']           = [];
        $doctorInfo['img_url']          = CommonImagesRepository::getField(['id' => $doctorInfo['img_id']],'img_url');
        $hospital                       = MediclaHospitalsRepository::getOne(['id' => $doctorInfo['hospitals_id']]);
        $department_arr = explode(',',$doctorInfo['department_ids']);
        if(!$department = MedicalDepartmentsRepository::getList(['id' => ['in',$department_arr]],['id','name'])){
            return [];
        }
        $labels_arr = explode(',',$doctorInfo['label_ids']);
        if(!$labels = MedicalDoctorLabelsRepository::getList(['id' => ['in',$labels_arr]],['id','name'])){
            return [];
        }
        $doctorInfo['departments']    = $department;
        $doctorInfo['labels']         = $labels;
        $doctorInfo['sex_name']       = DoctorEnum::getSex($doctorInfo['sex']);
        $doctorInfo['hospital_name']  = $hospital['name'];

        unset($doctorInfo['created_at'],$doctorInfo['updated_at'],
              $doctorInfo['img_id'],    $doctorInfo['sex'],
              $doctorInfo['member_id'], $doctorInfo['recommend'],
              $doctorInfo['label_ids'], $doctorInfo['department_ids'],
              $doctorInfo['deleted_at']);

        $this->setMessage('获取成功!');
        return $doctorInfo;
    }

    /**
     * 用户搜索 获取医生或者医院列表
     * @param $request
     * @return bool|mixed|null
     */
    public function searchDoctorsHospitals($request)
    {
        if ($request['type'] == 1){ //搜索医院  1医院  2医生
            $hospitals = new HospitalsService();
            if (!$list = $hospitals->getHospitalList($request)){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            $doctors = new DoctorsService();
            if (!$list = $doctors->getDoctorsListPage($request)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 用户根据科室获取医生列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getDepartmentsDoctor($request)
    {
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        if (!MedicalDepartmentsRepository::exists(['id' => $request['departments_id']])){
            $this->setError('科室不存在!');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospital_id'],'deleted_at' => 0])){
            $this->setError('医院不存在!');
            return false;
        }
        $column = ['id','name','title','good_at','label_ids','department_ids','img_id'];
        $check_where = ['hospitals_id' => $request['hospital_id'],'deleted_at' => 0,'department_ids' => ['like','%,' . $request['departments_id'] . ',%']];
        if (!$list = MedicalDoctorsRepository::getList($check_where,$column,'id','asc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }

        $list['data']    = ImagesService::getListImages($list['data'],['img_id' => 'single']);
        $department_ids  = array_column($list['data'],'department_ids');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        foreach ($list['data'] as &$value){
            $value['departments']    = [];
            $department_arr = explode(',',$value['department_ids']);
            foreach ($department_arr as $item){
                if ($department = $this->searchArray($department_list,'id',$item)){
                    $value['departments'][] = reset($department);
                }
            }
            unset($value['img_id'],$value['department_ids']);
        }

        $this->setMessage('获取成功!');
        return $list;
    }
}
            