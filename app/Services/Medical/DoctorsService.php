<?php
namespace App\Services\Medical;


use App\Repositories\CommonImagesRepository;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class DoctorsService extends BaseService
{
    use HelpTrait;


    public function addDoctors($request)
    {
        $label_ids = explode(',',$request['department_ids']);
        if (count($label_ids) != MedicalDepartmentsRepository::exists(['id' => ['in',$label_ids]])){
            $this->setError('科室不存在！');
            return false;
        }
        $add_arr = [
            'name'              => $request['name'],
            'title'             => $request['title'],
            'sex'               => $request['sex'],
            'good_at'           => $request['good_at'],
            'introduction'      => $request['introduction'],
            'label_ids'         => $request['label_ids'] ?? '',
            'hospitals_id'      => $request['hospitals_id'],
            'department_ids'    => $request['department_ids'] ?? '',
        ];
        if (MedicalDoctorsRepository::exists(['name' => $request['name']])){
            $this->setError('医生已存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $add_arr['hospitals_id']])){
            $this->setError('医院不存在！');
            return false;
        }

        if ($memberInfo = MemberRepository::getOne(['m_cname' => $request['name']])){
            $add_arr['member_id'] = $memberInfo['m_id'];
        }
        $add_arr['member_id']  = 0;
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
        if (!MedicalDoctorsRepository::exists(['id' => $id])){
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
        $add_arr = [
            'name'              => $request['name'],
            'title'             => $request['title'],
            'sex'               => $request['sex'],
            'good_at'           => $request['good_at'],
            'introduction'      => $request['introduction'],
            'label_ids'         => $request['label_ids'] ?? '',
            'hospitals_id'      => $request['hospitals_id'],
            'department_ids'    => $request['department_ids'] ?? '',
        ];
        if (MedicalDoctorsRepository::exists(['name' => $request['name']])){
            $this->setError('医生已存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $add_arr['hospitals_id']])){
            $this->setError('医院不存在！');
            return false;
        }

        if ($memberInfo = MemberRepository::getOne(['m_cname' => $request['name']])){
            $add_arr['member_id'] = $memberInfo['m_id'];
        }
        $add_arr['member_id']  = 0;
        $add_arr['updated_at'] = time();
        $add_arr['recommend']  = $request['recommend'] == 1 ? time() : 0;
        if (MedicalDoctorsRepository::getUpdId(['id' => $request['id']],$add_arr)){
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
            //if ($list = MedicalDoctorsRepository::getList($where,['*'],'created_at',$asc,$page,$page_num)){
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

        foreach ($list['data'] as &$value){
            //$department_ids        = explode(',',$value['department_ids']);
            //$img_ids               = array_column($department_ids,'');dd($img_ids);
            //$department_list       = MedicalDepartmentsRepository::getList(['id' => ['in',$department_ids]],['id','img_url']);
            $img_list        = CommonImagesRepository::getList(['id' => $value['img_id']],['id','img_url']);
            $value['hospitals_url']          = '';
            if ($hospitals_img = $this->searchArray($img_list,'id',$value['img_id'])){
                $value['hospitals_url']     = reset($hospitals_img)['img_url'];
            }

        }

        $this->setMessage('获取成功!');
        return $list;

    }
}
            