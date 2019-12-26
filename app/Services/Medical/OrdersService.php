<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MedicalOrdersViewRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Repositories\MemberBaseRepository;
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
            'member_id'          =>  $memberInfo->id,
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
        if (!$order = MedicalOrdersRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('订单不存在！');
            return false;
        }
        if ($order['status'] != DoctorEnum::SUBMIT){
            $this->setError('您的预约已审核,不能修改');
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
        $upd_arr = [
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
        if (MedicalOrdersRepository::exists(array_merge(['id' => ['<>',$request['id']]],$upd_arr))){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        if ($upd_arr['appointment_at'] < time()){
            $this->setError('不能预约已经逝去的日子!');
            return false;
        }
        if (!empty($upd_arr['end_time']) && ($upd_arr['end_time'] < $upd_arr['appointment_at'])){
            $this->setError('截止时间必须大于预约时间!');
            return false;
        }
        $add_arr['updated_at'] = time();
        if (!$orderId = MedicalOrdersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改预约失败!');
            return false;
        }
        $this->setMessage('修改预约成功');
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
        $status = $request['status'] == 1 ? DoctorEnum::PASS : DoctorEnum::NOPASS;
        $upd_arr = [
            'status'      => $status,
            'updated_at'  => time(),
        ];
        if (!$updOrder = MedicalOrdersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        #通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $orderInfo['member_id']])){
            $member_name = $orderInfo['name'];
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
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
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
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
        $member    = $this->auth->user();
        $where     = ['member_id' => $member->id, 'deleted_at' => 0];
        if (!$list = MedicalOrdersViewRepository::getList($where,['*'],'id','desc')) {
            $this->setMessage('暂时没有预约订单');
            return [];
        }
        if (empty($list)) {
            $this->setError('暂时没有预约订单');
            return false;
        }
        $doctor_ids = array_column($list, 'doctor_id');
        $doctors_list = MedicalDoctorsRepository::getAssignList($doctor_ids, ['id', 'department_ids']);
        $department_ids = array_column($doctors_list, 'department_ids');
        $department_list = MedicalDepartmentsRepository::getAssignList($department_ids, ['id', 'name']);
        foreach ($list as &$value) {
            $value['department_name'] = '';
            if ($department = $this->searchArray($department_list, 'id', $value['id'])) {
                $value['department_name'] = reset($department)['name'];
            }
            $value['sex_name']    = DoctorEnum::getSex($value['sex']);
            $value['status_name'] = DoctorEnum::getStatus($value['status']);
            $value['type_name']   = DoctorEnum::getType($value['type']);
            unset($value['hospital_id'], $value['img_id'], $value['doctor_id'], $value['member_id'],
                $value['type'], $value['department_ids'], $value['member_name'],$value['sex'],
                $value['created_at'], $value['updated_at'], $value['deleted_at']
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
        $keywords       = $data['keywords'] ?? null;
        $hospital_id    = $data['hospital_id'] ?? null;
        $department_id  = $data['department_id'] ?? null;
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $where          = ['deleted_at' => 0,];
        $column         = ['id','name','img_id','title','good_at','introduction','hospitals_id','department_ids'];
        if (!empty($hospital_id)){
            if (!MediclaHospitalsRepository::exists(['id' => $hospital_id])){
                $this->setError('医院不存在!');
                return false;
            }
            $where['hospitals_id'] = $hospital_id;
        }
        if (!empty($department_id)){
            if (!MedicalDepartmentsRepository::exists(['id' => $department_id])){
                $this->setError('科室不存在!');
                return false;
            }
            $where['department_ids'] = ['like','%,'.$department_id.',%'];
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','title','good_at']];
            if (!$list = MedicalDoctorsRepository::search($keyword,$where,$column,$page,$page_num,'id','desc')){
                $this->setError('获取失败');
                return false;
            }
        }else{
            if (!$list = MedicalDoctorsRepository::getList($where,$column,'id','desc',$page,$page_num)){
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
        if (!$orderInfo = MedicalOrdersViewRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('没有此订单!');
            return false;
        }
        $orderInfo['status_name']     = DoctorEnum::getStatus($orderInfo['status']);
        $orderInfo['type_name']       = DoctorEnum::getType($orderInfo['type']);
        $orderInfo['sex_name']        = DoctorEnum::getSex($orderInfo['sex']);
        $orderInfo['appointment_at']  = date('Y-m-d H:m',strtotime($orderInfo['appointment_at']));
        $orderInfo['end_time']        = date('Y-m-d H:m',strtotime($orderInfo['end_time']));
        unset($orderInfo['member_id'],$orderInfo['hospital_id'],$orderInfo['created_at'],$orderInfo['updated_at'],$orderInfo['deleted_at']);
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
        if (!MedicalOrdersRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('预约不存在!');
            return false;
        }
        if (!MedicalOrdersRepository::getUpdId(['id' => $request['id']],['deleted_at' => time()])){
            $this->setError('取消预约失败!');
            return false;
        }
        $this->setMessage('取消预约成功');
        return true;
    }

    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = MedicalOrdersRepository::count(['deleted_at' => 0]) ?? 0;
        $audit_count    = MedicalOrdersRepository::count(['deleted_at' => 0,'status' => ['in',[DoctorEnum::PASS,DoctorEnum::NOPASS]]]) ?? 0;
        $no_audit_count = MedicalOrdersRepository::count(['deleted_at' => 0,'status' => DoctorEnum::SUBMIT]) ?? 0;
        $cancel_count   = 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }

    /**
     * 获取申请人ID
     * @param $order_id
     * @return mixed
     */
    public function getCreatedUser($order_id){
        return MedicalOrdersRepository::getField(['id',$order_id],'member_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $order_ids
     * @return mixed
     */
    public function getProcessBusinessList($order_ids){
        if (empty($order_ids)){
            return [];
        }
        $column     = ['id','member_id','name','mobile'];
        if (!$order_list = MedicalOrdersRepository::getAssignList($order_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => '医疗预约',
                'member_id'     => $value['member_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            