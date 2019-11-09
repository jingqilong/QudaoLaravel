<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalHospitalRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
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
     * @return array|bool
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
            'age'                =>  $request['age'],
            'hospital_id'        =>  $request['hospital_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'description'        =>  $request['description'],
            'status'             =>  DoctorEnum::SUBMIT,
            'type'               =>  $request['type'],
            'created_at'         =>  time(),
            'appointment_at'     =>  strtotime($request['appointment_at']),
            'end_time'           =>  isset($request['end_time']) ? strtotime($request['end_time']) : 0,
        ];
        if ($add_arr['appointment_at'] < time()){
            $this->setError('不能预约已经逝去的日子!');
            return false;
        }
        if (!empty($add_arr['end_time']) && ($add_arr['end_time'] < $add_arr['appointment_at'])){
            $this->setError('截止时间必须大于预约时间!');
            return false;
        }
        if (MedicalOrdersRepository::exists($add_arr)){
            $this->setError('预约已提交!');
            return false;
        }
        if (!MedicalOrdersRepository::getAddId($add_arr)){
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
        $type           = $data['type'] ?? null;
        $column         = ['*'];
        $where          = ['deleted_at' => 0];
        if ($status !== null){
            $where['status'] = $status;
        }
        if ($type !== null){
            $where['type'] = $type;
        }
        if (!empty($keywords)) {
            $keyword = [$keywords => ['name', 'mobile']];
            if (!$list = MedicalOrdersRepository::search($keyword, $where, $column, $page, $page_num, 'created_at', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MedicalOrdersRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('没有数据!');
            return [];
        }
        $doctor_ids      = array_column($list['data'],'doctor_id');
        $hospitals_ids   = array_column($list['data'],'hospital_id');
        $doctor_list     = MedicalDoctorsRepository::getAssignList($doctor_ids,['id','name']);
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);

        foreach ($list['data'] as &$value){
            $value['doctor_name']    = '';
            $value['hospital_name']  = '';
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospital_id'])){
                $value['hospital_name'] = reset($hospitals)['name'];
            }
            if ($doctor = $this->searchArray($doctor_list,'id',$value['doctor_id'])){
                $value['doctor_name'] = reset($doctor)['name'];
            }
            $value['status_name']       =  DoctorEnum::getStatus($value['status']);
            $value['sex_name']          =  DoctorEnum::getSex($value['sex']);
            $value['type_name']         =  DoctorEnum::getType($value['type']);
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
                $content = '您好！您预约的《'.$hospital['name'].'》,《'.$doctor['name'].'》医生专诊,未通过审核,请您联系客服0000-00000再次预约，谢谢！';
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
     * @return array|bool|null
     */
    public function doctorsOrderList()
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
        $hospitals_ids   = array_column($list,'hospital_id');
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);
        $doctor_ids   = array_column($list,'doctor_id');
        $doctors_list  = MedicalDoctorsRepository::getAssignList($doctor_ids,['id','name']);

        foreach ($list as &$value){
            $value['hospitals_name'] = '';
            $value['doctors_name'] = '';
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospital_id'])){
                $value['hospitals_name'] = reset($hospitals)['name'];
            }
            if ($doctors = $this->searchArray($doctors_list,'id',$value['doctor_id'])){
                $value['doctors_name'] = reset($doctors)['name'];
            }
            $value['status']    = DoctorEnum::getStatus($value['status']);
            $value['type']      = DoctorEnum::getType($value['type']);
            unset($value['hospital_id'],$value['img_id'],$value['doctor_id'],$value['member_id'],
                  $value['status'],$value['type'],$value['department_ids'],
                  $value['created_at'],$value['updated_at'],$value['deleted_at'],
            );
        }

        $this->setMessage('获取成功！');
        return $list;
    }


    /**
     * 成员获取医生列表
     * @param array $data
     * @return bool|mixed|null
     */
    public function doctorsList(array $data)
    {
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $column         = ['id','name','img_id','title','good_at','introduction','hospitals_id','department_ids'];
        if (!empty($data['hospital_id'])){
            if (!$list = MedicalDoctorsRepository::getList(['deleted_at' => 0,'hospitals_id' => $data['hospital_id']],$column,'id','desc',$page,$page_num)){
                $this->setError('获取失败');
                return false;
            }
        }else{
            if (!$list = MedicalDoctorsRepository::getList(['deleted_at' => 0],$column,'id','desc',$page,$page_num)){
                $this->setError('获取失败');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        $list['data']    = ImagesService::getListImages($list['data'],['img_id' => 'single']);
        $department_ids  = array_column($list['data'],'department_ids');
        $hospitals_ids   = array_column($list['data'],'hospitals_id');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);

        foreach ($list['data'] as &$value){
            $value['departments']    = [];
            $value['hospitals_name'] = '';
            $department_arr = explode(',',$value['department_ids']);
            foreach ($department_arr as $item){
                if ($department = $this->searchArray($department_list,'id',$item)){
                    $value['departments'][] = reset($department);
                }
            }
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospitals_id'])){
                $value['hospitals_name'] = reset($hospitals)['name'];
            }
            unset($value['hospitals_id'],$value['img_id'],$value['department_ids']);
        }

        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     *根据id获取成员自己预约详情
     * @param $request
     * @return bool|null
     */
    public function doctorsOrder($request)
    {
        if (!$orderInfo = MedicalOrdersRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('没有此订单!');
            return false;
        }
        if (!$hospital = MediclaHospitalsRepository::getOne(['id' => $orderInfo['hospital_id']])){
            $this->setError('无效订单!');
            return false;
        }
        if (!$doctor = MedicalDoctorsRepository::getOne(['id' => $orderInfo['doctor_id']])){
            $this->setError('无效订单!');
            return false;
        }
        $orderInfo['hospital_name']   = $hospital['name'];
        $orderInfo['doctor_name']     = $doctor['name'];
        $orderInfo['status_name']     = DoctorEnum::getStatus($orderInfo['status']);
        $orderInfo['type_name']       = DoctorEnum::getType($orderInfo['type']);
        $orderInfo['sex_name']        = DoctorEnum::getSex($orderInfo['sex']);
        unset($orderInfo['member_id'],$orderInfo['doctor_id'],
              $orderInfo['sex'],      $orderInfo['hospital_id'],
              $orderInfo['status'],   $orderInfo['type']);
        $this->setMessage('查找成功!');
        return $orderInfo;
    }

}
            