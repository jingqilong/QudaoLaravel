<?php
namespace App\Services\Medical;


use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class DoctorsService extends BaseService
{
    use HelpTrait;


    public function addDoctors($request)
    {

        $add_arr = [
            'name'              => $request['name'],
            'title'             => $request['title'],
            'sex'               => $request['sex'],
            'good_at'           => $request['good_at'],
            'introduction'      => $request['introduction'],
            'recommend'         => $request['introduction'],
            'hospitals_id'      => $request['hospitals_id'],
            'department_id'     => $request['department_id'],
        ];
        if (MedicalDoctorsRepository::exists(['name' => $request['name']])){
            $this->setError('医生已存在！');
            return false;
        }

        if ($memberInfo = MemberRepository::getOne(['m_cname' => $request['name']])){
            $add_arr['member_id'] = $memberInfo['m_id'];
        }
        $add_arr['member_id']  = 0;
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        $add_arr['recommend']   = $request['recommend'] == 1 ? time() : 0;
        if (MedicalDoctorsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            