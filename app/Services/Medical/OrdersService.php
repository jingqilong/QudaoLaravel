<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
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
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospital_id'],'deleted_at' => 0])){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::getOne(['id' => $request['doctor_id'],'deleted_at' => 0])){
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
            'description'        =>  $request['description'] ?? '',
            'type'               =>  $request['type'],
            'appointment_at'     =>  strtotime($request['appointment_at']),
            'end_time'           =>  isset($request['end_time']) ? strtotime($request['end_time']) : 0,
        ];
        if (MedicalOrdersRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        if ($add_arr['appointment_at'] < time()){
            $this->setError('不能预约已经逝去的日子!');
            return false;
        }
        if (!empty($add_arr['end_time']) && ($add_arr['end_time'] < $add_arr['appointment_at'])){
            $this->setError('截止时间必须大于预约时间!');
            return false;
        }
        $add_arr['created_at'] = time();
        if (!$orderId = MedicalOrdersRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            return false;
        }
        $this->setMessage('预约成功');
        return $orderId;
    }

    /**
     * 修改预约订单
     * @param $request
     * @return array|bool
     */
    public function editDoctorOrders($request)
    {
        if (!MedicalOrdersRepository::exists(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('订单不存在！');
            return false;
        }
        if (!MediclaHospitalsRepository::exists(['id' => $request['hospital_id'],'deleted_at' => 0])){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::getOne(['id' => $request['doctor_id'],'deleted_at' => 0])){
            $this->setError('医生不存在！');
            return false;
        }
        $add_arr = [
            'name'               =>  $request['name'],
            'mobile'             =>  $request['mobile'],
            'sex'                =>  $request['sex'],
            'age'                =>  $request['age'],
            'hospital_id'        =>  $request['hospital_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'description'        =>  $request['description'] ?? '',
            'type'               =>  $request['type'],
            'appointment_at'     =>  strtotime($request['appointment_at']),
            'end_time'           =>  isset($request['end_time']) ? strtotime($request['end_time']) : 0,
        ];
        if (MedicalOrdersRepository::getOne(['id' => ['<>',$request['id']]],$add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        if ($add_arr['appointment_at'] < time()){
            $this->setError('不能预约已经逝去的日子!');
            return false;
        }
        if (!empty($add_arr['end_time']) && ($add_arr['end_time'] < $add_arr['appointment_at'])){
            $this->setError('截止时间必须大于预约时间!');
            return false;
        }
        $add_arr['created_at'] = time();
        if (!$orderId = MedicalOrdersRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            return false;
        }
        $this->setMessage('预约成功');
        return $orderId;
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
        if (!$orderInfo = MedicalOrdersRepository::getOne(['id' => $request['id']])){
            $this->setError('无此订单!');
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
        if (!$updOrder = MedicalOrdersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        #通知用户
        $status = $upd_arr['status'];
        if ($member = MemberRepository::getOne(['m_id' => $orderInfo['member_id']])){
            $member_name = $orderInfo['name'];
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
            $sms_template = [
                DoctorEnum::PASS   =>
                    MessageEnum::getTemplate(
                        MessageEnum::MEDICALBOOKING,
                        'auditPass',
                        ['member_name' => $member_name,'doctor_name' => $doctor['name']]
                    ),
                DoctorEnum::NOPASS =>
                    MessageEnum::getTemplate(
                        MessageEnum::MEDICALBOOKING,
                        'auditNoPass',
                        ['member_name' => $member_name,'doctor_name' => $doctor['name']]
                    ),
            ];
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
            }
            $title = '医疗预约通知';
            #发送站内信
            SendService::sendMessage($orderInfo['member_id'],MessageEnum::MEDICALBOOKING,$title,$sms_template[$status],$request['id']);
        }
        $this->setMessage('审核成功！');
        return true;

    }

    /**
     * 获取成员自己预约列表状态
     * @return array|bool|null
     */
    public function doctorsOrderList()
    {
        $memberInfo = $this->auth->user();
        if (!$list = MedicalOrdersRepository::getList(['member_id' => $memberInfo['m_id'],'deleted_at' => 0])){
            $this->setMessage('暂时没有预约订单');
            return [];
        }
        if (empty($list)){
            $this->setError('暂时没有预约订单');
            return false;
        }
        $hospitals_ids   = array_column($list,'hospital_id');
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);
        $doctor_ids      = array_column($list,'doctor_id');
        $doctors_list    = MedicalDoctorsRepository::getAssignList($doctor_ids,['id','name','department_ids','img_id']);
        $doctors_img     = ImagesService::getListImagesConcise($doctors_list,['img_id' => 'single']);
        $doctor_id       = array_column($list,'doctor_id');
        $doctors_name    = MedicalDoctorsRepository::getAssignList($doctor_id,['id','title']);
        $department_ids      = array_column($doctors_list,'department_ids');
        $department_list    = MedicalDepartmentsRepository::getAssignList($department_ids,['id','name']);
        foreach ($list as &$value){
            $value['hospitals_name'] = '';
            $value['department_name'] = '';
            $value['doctors_name'] = '';
            $value['doctors_title'] = '';
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospital_id'])){
                $value['hospitals_name'] = reset($hospitals)['name'];
            }
            if ($doctors = $this->searchArray($doctors_list,'id',$value['doctor_id'])){
                $value['doctors_name'] = reset($doctors)['name'];
            }
            if ($doctors_title= $this->searchArray($doctors_name,'id',$value['id'])){
                $value['doctors_title'] = reset($doctors_title)['title'];
            }
            if ($department = $this->searchArray($department_list,'id',$value['id'])){
                $value['department_name'] = reset($department)['name'];
            }
            if ($doctors_image = $this->searchArray($doctors_img,'id',$value['id'])){
                $value['img_url'] = reset($doctors_image)['img_url'];
            }
            $value['status_name']    = DoctorEnum::getStatus($value['status']);
            $value['type']           = DoctorEnum::getType($value['type']);
            unset($value['hospital_id'],$value['img_id'],$value['doctor_id'],$value['member_id'],
                  $value['type'],$value['department_ids'],
                  $value['created_at'],$value['updated_at'],$value['deleted_at']
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
        $orderInfo['hospital_name']   = MediclaHospitalsRepository::getField(['id' => $orderInfo['hospital_id']],'name');
        $orderInfo['doctor_name']     = MedicalDoctorsRepository::getField(['id' => $orderInfo['doctor_id']],'name');
        $orderInfo['doctor_title']    = MedicalDoctorsRepository::getField(['id' => $orderInfo['doctor_id']],'title');
        $doctor                       = MedicalDoctorsRepository::getOne(['id' => $orderInfo['doctor_id']]);
        $doctor                       = ImagesService::getOneImagesConcise($doctor,['img_id' => 'single']);
        $orderInfo['image_url']       = $doctor['img_url'];
        $department_ids               = explode(',',$doctor['department_ids']);
        $orderInfo['department_name'] = MedicalDepartmentsRepository::getList(['id' => ['in',$department_ids]],['id','name']);
        $orderInfo['status_name']     = DoctorEnum::getStatus($orderInfo['status']);
        $orderInfo['type_name']       = DoctorEnum::getType($orderInfo['type']);
        $orderInfo['sex_name']        = DoctorEnum::getSex($orderInfo['sex']);
        unset($orderInfo['member_id'],$orderInfo['sex'],$orderInfo['hospital_id'],
              $orderInfo['type']);
        $this->setMessage('查找成功!');
        return $orderInfo;
    }

    /**
     * 取消预约订单
     * @param $request
     * @return bool
     */
    public function delDoctorOrder($request)
    {
        if (!MedicalOrdersRepository::getOne(['id' => $request['id']])){
            $this->setError('订单不存在!');
            return false;
        }
        if (!MedicalOrdersRepository::getUpdId(['id' => $request['id']],['deleted_at' => time()])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }

}
            