<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
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
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospital_id']])){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::getOne(['id' => $request['doctor_id']])){
            $this->setError('医生不存在！');
            return false;
        }
        $add_arr = [
            'member_id'          =>  $memberInfo->m_id,
            'name'               =>  $request['name'],
            'mobile'             =>  $request['mobile'],
            'sex'                =>  $request['sex'],
            'hospital_id'        =>  $request['hospital_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'description'        =>  $request['description'],
            'status'             =>  DoctorEnum::SUBMIT,
            'appointment_at'     =>  strtotime($request['appointment_at'] ?? 0),
            'end_time'           =>  strtotime($request['end_time'] ?? 0),
            'created_at'         =>  strtotime($request['created_at'] ?? 0),
        ];
        if (!$res = MedicalOrdersRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            return false;
        }
        $this->setMessage('预约成功');
        return true;
    }

    /**
     * 获取预约列表(oa)
     * @param $data
     * @return bool|mixed|null
     */
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
        if (empty($list['data'])){
            $this->setError('没有数据!');
            return false;
        }
        $list = $this->removePagingField($list);
        foreach ($list['data'] as &$value){
            if ($doctor_name = MedicalDoctorsRepository::getOne(['id' => $value['doctor_id']])){
                $value['doctor_name'] = $doctor_name['name'];
            }
            if ($hospitals_name = MediclaHospitalsRepository::getOne(['id' => $value['hospital_id']])){
                $value['hospitals_name'] = $hospitals_name['name'];
            }
            $value['status_name']       =  DoctorEnum::getStatus($value['status']);
            $value['sex_name']          =  DoctorEnum::getSex($value['sex']);
        }

        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 审核预约列表状态(oa)
     * @param $request
     * @return bool|null
     */
    public function setDoctorOrder($request)
    {

        if (!MedicalOrdersRepository::exists(['id' => $request['id']])){
            $this->setError('无此订单!');
            return false;
        }
        if (!$orderInfo = MedicalOrdersRepository::getOne(['id' => $request['id']])){
            $this->setError('无此订单!');
            return false;
        }
        if (!$hospital = MediclaHospitalsRepository::getOne(['id' => $orderInfo['hospital_id']])){
            $this->setError('无此医院!');
            return false;
        }
        if (!$doctor = MedicalDoctorsRepository::getOne(['id' => $orderInfo['doctor_id']])){
            $this->setError('无此医生!');
            return false;
        }
        $upd_arr = [
            'status'      => $request['status'] == 1 ? DoctorEnum::PASS : DoctorEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if ($updOrder = MedicalOrdersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            if ($request['status'] == DoctorEnum::PASS){
                //TODO 此处可以添加报名后发通知的事务
                #发送短信
                if (!empty($orderInfo)){
                    $sms = new SmsService();
                    $content = '您好！您预约的《'.$hospital['name'].'》,《'.$doctor['name'].'》医生专诊,已通过审核,我们将在24小时内负责人联系您,请保持消息畅通，谢谢！';
                    $sms->sendContent($orderInfo['mobile'],$content);
                }
                $this->setMessage('审核通过,消息已发送给联系人！');
                return $updOrder;
            }
            //TODO 此处可以添加报名后发通知的事务
            #发送短信
            if (!empty($orderInfo)){
                $sms = new SmsService();
                $content = '您好！您预约的《'.$hospital['name'].'》,《'.$doctor['name'].'》医生专诊,没有通过审核,请您联系客服0000-00000再次预约，谢谢！';
                $sms->sendContent($orderInfo['mobile'],$content);
            }
            $this->setMessage('审核失败,消息已发送给联系人！');
            return $updOrder;
        }
        $this->setError('审核失败，请重试!');
        return false;
    }

    /**
     * 获取成员自己预约列表状态
     * @param $request
     * @return array|bool|null
     */
    public function doctorsList($request)
    {
        $memberInfo = $this->auth->user();
        if (!$list = MedicalOrdersRepository::getList(['member_id' => $memberInfo['m_id']])){
            $this->setMessage('暂时没有预约订单');
            return [];
        }
        if (empty($list)){
            $this->setError('暂时没有预约订单');
            return false;
        }
        foreach ($list as &$value){
            if ($doctor_name = MedicalDoctorsRepository::getOne(['id' => $value['doctor_id']])){
                $value['doctor_name'] = $doctor_name['name'];
            }
            if ($hospitals_name = MediclaHospitalsRepository::getOne(['id' => $value['hospital_id']])){
                $value['hospitals_name'] = $hospitals_name['name'];
            }
            $value['status_name']       =  DoctorEnum::getStatus($value['status']);
            $value['sex_name']          =  DoctorEnum::getSex($value['sex']);
        }

        $this->setMessage('获取成功！');
        return $list;
    }

}
            