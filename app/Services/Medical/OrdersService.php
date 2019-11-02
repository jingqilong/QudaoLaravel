<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OrdersService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }


    /**
     * 添加预约
     * @param $request
     * @return bool
     */
    public function addDoctorOrders($request)
    {
        $memberInfo = $this->auth->user();
        if (!MediclaHospitalsRepository::getOne($request(['hospitals_id']))){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::getOne($request(['doctor_id']))){
            $this->setError('医生不存在！');
            return false;
        }
        $add_arr = [
            'member_id'          =>  $memberInfo->m_id,
            'name'               =>  $request['name'],
            'sex'                =>  $request['sex'],
            'hospitals_id'       =>  $request['hospitals_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'description'        =>  $request['description'],
            'status'             =>  DoctorEnum::SUBMIT,
            'appointment_at'     =>  strtotime($request['appointment_at']),
            'end_time'           =>  strtotime($request['end_time']),
            'created_at'         =>  strtotime($request['created_at']),
        ];
        if (!$res = MedicalOrdersRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            return false;
        }
        $this->setMessage('预约成功');
        return true;
    }

    public function getDoctorOrderList($data)
    {
        if (empty($data['asc'])){
            $data['asc']  = 1;
        }

        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $status         = $data['status'] ?? null;
        $column         = ['*'];
        $where          = ['deleted_at' => 0];
        if ($status !== null){
            $where['status'] = $status;
        }
        if (!empty($keywords)){
            $keyword        = [$keywords => ['name','mobile']];
            if(!$list = MedicalOrdersRepository::search($keyword,$where,$column,$page,$page_num,'created_at',$asc)){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MedicalOrdersRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $this->removePagingField($list);
        foreach ($list['data'] as &$value){
            if ($doctor_name = MedicalDoctorsRepository::getOne(['id' => $data['doctor_id']])){
                $value['doctor_name'] = $doctor_name['name'];
            }
            if ($doctor_name = MediclaHospitalsRepository::getOne(['id' => $data['hospitals_id']])){
                $value['doctor_name'] = $doctor_name['name'];
            }

            $value['status_name']       =  DoctorEnum::getStatus($value['status']);
            $value['sex_name']          =  DoctorEnum::getSex($value['sex']);
        }

        $this->setMessage('获取成功！');
        return $list;
    }

}
            