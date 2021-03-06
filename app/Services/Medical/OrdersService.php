<?php
namespace App\Services\Medical;


use App\Enums\DoctorEnum;
use App\Enums\MessageEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\MedicalDepartmentsRepository;
use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MedicalOrdersRepository;
use App\Repositories\MedicalOrdersViewRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersService extends BaseService
{
    use HelpTrait,BusinessTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }


    /**
     * 添加医疗预约信息
     * @param $request
     * @return array|bool
     */
    public function addDoctorOrders($request)
    {
        $memberInfo     = $this->auth->user();
        $end_time       = isset($request['end_time']) ? strtotime($request['end_time']) : 0;
        $appointment_at = strtotime($request['appointment_at']);

        if (!MediclaHospitalsRepository::exists(['id' => $request['hospital_id'],'deleted_at' => 0])){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::exists(['id' => $request['doctor_id'],'deleted_at' => 0])){
            $this->setError('医生不存在！');
            return false;
        }
        if (!MedicalDepartmentsRepository::exists(['id' => $request['departments_id']])){
            $this->setError('科室不存在！');
            return false;
        }
        if ($appointment_at < time()){
            $this->setError('不能预约已经逝去的日子!');
            return false;
        }
        if (!empty($end_time) && ($end_time < $appointment_at)){
            $this->setError('截止时间必须大于预约时间!');
            return false;
        }
        $add_arr = [
            'member_id'          =>  $memberInfo->id,
            'name'               =>  $request['name'],
            'mobile'             =>  $request['mobile'],
            'sex'                =>  $request['sex'],
            'age'                =>  $request['age'],
            'hospital_id'        =>  $request['hospital_id'],
            'departments_id'     =>  $request['departments_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'description'        =>  $request['description'] ?? '',
            'type'               =>  $request['type'],
            'appointment_at'     =>  $appointment_at,
            'end_time'           =>  $end_time,
        ];
        if (MedicalOrdersRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        $add_arr['created_at'] = time();
        DB::beginTransaction();
        if (!$orderId = MedicalOrdersRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            DB::rollBack();
            return false;
        }
        #开启流程
        $start_process_result = $this->addNewProcessRecord($orderId,ProcessCategoryEnum::LOAN_RESERVATION);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('预约成功');
        return $orderId;
    }

    /**
     * 修改医疗预约信息
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
        if (!MedicalDepartmentsRepository::exists(['id' => $request['departments_id']])){
            $this->setError('科室不存在！');
            return false;
        }
        $upd_arr = [
            'name'               =>  $request['name'],
            'mobile'             =>  $request['mobile'],
            'sex'                =>  $request['sex'],
            'age'                =>  $request['age'],
            'hospital_id'        =>  $request['hospital_id'],
            'doctor_id'          =>  $request['doctor_id'],
            'departments_id'     =>  $request['departments_id'],
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
        $employee = Auth::guard('oa_api')->user();
        if (empty($data['asc'])) $data['asc']  = 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $status         = $data['status'] ?? null;
        $type           = $data['type'] ?? null;
        $column         = ['*'];
        $where          = ['deleted_at' => 0];
        if ($status !== null) $where['status'] = $status;
        if ($type !== null) $where['type'] = $type;
        if (!empty($keywords)) {
            $keyword = [$keywords => ['name', 'mobile']];
            if (!$list = MedicalOrdersRepository::search($keyword, $where, $column,  'id', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MedicalOrdersRepository::getList($where,$column,'id',$asc)){
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
        $departments_ids = array_column($list['data'],'departments_id');
        $doctor_list     = MedicalDoctorsRepository::getAssignList($doctor_ids,['id','name']);
        $hospitals_list  = MediclaHospitalsRepository::getAssignList($hospitals_ids,['id','name']);
        $departments_list= MedicalDepartmentsRepository::getAssignList($departments_ids,['id','name']);
        foreach ($list['data'] as &$value){
            $value['doctor_name']    = '';
            $value['hospital_name']  = '';
            $value['departments_name']  = '';
            if ($hospitals = $this->searchArray($hospitals_list,'id',$value['hospital_id'])){
                $value['hospital_name'] = reset($hospitals)['name'];
            }
            if ($doctor = $this->searchArray($doctor_list,'id',$value['doctor_id'])){
                $value['doctor_name'] = reset($doctor)['name'];
            }
            if ($departments = $this->searchArray($departments_list,'id',$value['departments_id'])){
                $value['departments_name'] = reset($departments)['name'];
            }
            $value['status_name']       =  DoctorEnum::getStatus($value['status']);
            $value['sex_name']          =  DoctorEnum::getSex($value['sex']);
            $value['type_name']         =  DoctorEnum::getType($value['type']);
            $value['appointment_at']    = empty($value['appointment_at']) ? '' : date('Y-m-d',$value['appointment_at']);
            $value['created_at']        = empty($value['created_at']) ? '' : date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at']        = empty($value['updated_at']) ? '' : date('Y-m-d H:i:s',$value['updated_at']);
            $value['end_time']          = empty($value['end_time']) ? '' : date('Y-m-d H:i:s',$value['end_time']);
            unset($value['deleted_at']);
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::HOSPITAL_RESERVATION,$employee->id);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取预约详情
     * @param $id
     * @return array|bool
     */
    public function getOrderDetail($id){
        $employee = Auth::guard('oa_api')->user();
        $column = ['id','name','mobile','sex','age','type','end_time','hospital_id','doctor_id','departments_id','appointment_at','status','created_at','updated_at'];
        if (!$info = MedicalOrdersRepository::getOne(['id' => $id],$column)){
            $this->setError('预约信息不存在!');
            return false;
        }
        $info['doctor_name']        = MedicalDoctorsRepository::getField(['id' => $info['doctor_id']],'name');
        $info['hospital_name']      = MediclaHospitalsRepository::getField(['id' => $info['hospital_id']],'name');
        $info['departments_name']   = MedicalDepartmentsRepository::getField(['id' => $info['departments_id']],'name');
        $info['status']             = DoctorEnum::getStatus($info['status']);
        $info['sex']                = DoctorEnum::getSex($info['sex']);
        $info['type']               = DoctorEnum::getType($info['type']);
        $info['appointment_at']     = empty($info['appointment_at']) ? '' : date('Y-m-d',$info['appointment_at']);
        $info['created_at']         = empty($info['created_at']) ? '' : date('Y-m-d H:i:s',$info['created_at']);
        $info['updated_at']         = empty($info['updated_at']) ? '' : date('Y-m-d H:i:s',$info['updated_at']);
        $info['end_time']           = empty($info['end_time']) ? '' : date('Y-m-d H:i:s',$info['end_time']);
        unset($info['doctor_id'],$info['hospital_id']);
        return $this->getBusinessDetailsProcess($info,ProcessCategoryEnum::HOSPITAL_RESERVATION,$employee->id);
    }

    /**
     * 审核预约列表状态(oa)
     * @param $id
     * @param $audit
     * @return bool|null
     */
    public function setDoctorOrder($id,$audit)
    {
        if (!$orderInfo = MedicalOrdersRepository::getOne(['id' => $id])){
            $this->setError('查询不到预约信息!');
            return false;
        }
        if (!$doctor = MedicalDoctorsRepository::getOne(['id' => $orderInfo['doctor_id']])){
            $this->setError('预约医生不存在!');
            return false;
        }
        $status = $audit == 1 ? DoctorEnum::PASS : DoctorEnum::NOPASS;
        $upd_arr = [
            'status'      => $status,
            'updated_at'  => time(),
        ];
        if (!$updOrder = MedicalOrdersRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        #通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $orderInfo['member_id']])){
            $member_name = $orderInfo['name'];
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
            if (!empty($orderInfo['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($orderInfo['mobile'],$sms_template[$status]);
            }
            $title = '医疗预约通知';
            #发送站内信
            SendService::sendMessage($orderInfo['member_id'],MessageEnum::MEDICALBOOKING,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return true;

    }

    /**
     * 获取成员自己预约列表状态（修改）
     * @return array|bool|null
     */
    public function doctorsOrderList()
    {
        $member    = $this->auth->user();
        $where     = ['member_id' => $member->id, 'deleted_at' => 0];
        $column    = ['id','name','mobile','longitude','latitude','sex','age','type','end_time','hospital_name','departments_name','doctor_head_url','doctor_name','doctor_title','appointment_at','status','created_at'];
        if (!$list = MedicalOrdersViewRepository::getAllList($where,$column,'id','desc')) {
            $this->setMessage('暂时没有预约订单');
            return [];
        }
        foreach ($list as &$value) {
            $value['sex_name']    = DoctorEnum::getSex($value['sex']);
            $value['status_name'] = DoctorEnum::getStatus($value['status']);
            $value['type_name']   = DoctorEnum::getType($value['type']);
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
            if (!$list = MedicalDoctorsRepository::search($keyword,$where,$column,'id','desc')){
                $this->setError('获取失败');
                return false;
            }
        }else{
            if (!$list = MedicalDoctorsRepository::getList($where,$column,'id','desc')){
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
     * 医疗根据id获取成员自己预约详情
     * @param $request
     * @return bool|null
     */
    public function doctorsOrder($request)
    {
        $column = ['id','name','mobile','longitude','latitude','sex','age','type','description','end_time','hospital_id','hospital_name','departments_id','departments_name','doctor_id','doctor_head_url','doctor_name','doctor_title','appointment_at','status','created_at','updated_at'];
        if (!$orderInfo = MedicalOrdersViewRepository::getOne(['id' => $request['id'],'deleted_at' => 0],$column)){
            $this->setError('没有此订单!');
            return false;
        }
        $department_ids               = MedicalDoctorsRepository::getField(['id' => $orderInfo['doctor_id']],'department_ids');
        $department_list              = explode(',',trim($department_ids,','));
        $orderInfo['department_arr']  = MedicalDepartmentsRepository::getAllList(['id' => ['in',$department_list]],['id','name']);
        $orderInfo['status_name']     = DoctorEnum::getStatus($orderInfo['status']);
        $orderInfo['type_name']       = DoctorEnum::getType($orderInfo['type']);
        $orderInfo['sex_name']        = DoctorEnum::getSex($orderInfo['sex']);
        $orderInfo['appointment_at']  = date('Y-m-d H:m',strtotime($orderInfo['appointment_at']));
        $orderInfo['end_time']        = date('Y-m-d H:m',strtotime($orderInfo['end_time']));
        unset($orderInfo['member_id'],$orderInfo['created_at'],$orderInfo['updated_at'],$orderInfo['deleted_at']);
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
        DB::beginTransaction();
        if (!MedicalOrdersRepository::getUpdId(['id' => $request['id']],['deleted_at' => time()])){
            $this->setError('取消预约失败!');
            DB::rollBack();
            return false;
        }
        if (!$this->cancelBusinessProcess($request['id'],ProcessCategoryEnum::HOSPITAL_RESERVATION)){
            $this->setError('取消预约失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
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
        return MedicalOrdersRepository::getField(['id' => $order_id],'member_id');
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
            