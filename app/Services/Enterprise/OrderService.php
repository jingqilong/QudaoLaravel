<?php
namespace App\Services\Enterprise;



use App\Enums\EnterEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\EnterpriseOrderRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{

    use HelpTrait,BusinessTrait;
    public $auth;

    /**
     * PrizeService constructor.
     * @param $auth
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 获取项目对接订单列表  （前端使用）
     * @return mixed
     */
    public function getEnterpriseList()
    {
        $member = $this->auth->user();
        $where  = ['deleted_at' => 0,'user_id' => $member->id];
        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at','deleted_at'];
        if (!$list = EnterpriseOrderRepository::getList($where,$column,'created_at','desc')){
            $this->setMessage('没有数据！');
            return [];
        }
        if (empty($list)){
            $this->setMessage('没有数据！');
            return $list;
        }
        $list = $this->removePagingField($list);
        foreach ($list as &$value)
        {
            $value['type_name']         =   EnterEnum::getType($value['service_type']);
            $value['status_name']       =   EnterEnum::getStatus($value['status']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 获取项目对接订单列表  （后端使用）
     * @param array $data
     * @return mixed
     */
    public function getEnterpriseOrderList(array $data)
    {
        $employee = Auth::guard('oa_api')->user();
        $keywords       = $data['keywords'] ?? null;
        $type           = $data['type'] ?? null;
        $status         = $data['status'] ?? null;
        $where          = ['deleted_at' => 0];
        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at'];
        if (!empty($type)) {
            $where['service_type'] = $type;
        }
        if (!empty($status)) {
            $where['status'] = $status;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['enterprise_name']];
            if (!$list = EnterpriseOrderRepository::search($keyword,$where,$column,'id','desc')){
                $this->setMessage('没有数据！');
                return [];
            }
        }else{
            if (!$list = EnterpriseOrderRepository::getList($where,$column,'id','desc')){
                $this->setError('没有数据！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('没有获取到数据！');
            return $list;
        }
        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   EnterEnum::getType($value['service_type']);
            $value['status_name']       =   EnterEnum::getStatus($value['status']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::ENTERPRISE_CONSULT,$employee->id);
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 获取企业咨询订单详情 OA
     * @param string $id
     * @return mixed
     */
    public function getEnterpriseDetail($id)
    {
        $employee = Auth::guard('oa_api')->user();
        if (!$info = EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('查询不到该条数据！');
            return false;
        }
        $info['service_type']       =   EnterEnum::getType($info['service_type']);
        $info['status']             =   EnterEnum::getStatus($info['status']);
        $info['reservation_at']     =   empty($info['reservation_at']) ? '' : date('Y-m-d H:m:s',$info['reservation_at']);
        $info['created_at']         =   empty($info['created_at']) ? '' : date('Y-m-d H:m:s',$info['created_at']);
        $info['updated_at']         =   empty($info['updated_at']) ? '' : date('Y-m-d H:m:s',$info['updated_at']);
        unset($info['deleted_at']);
        return $this->getBusinessDetailsProcess($info,ProcessCategoryEnum::ENTERPRISE_CONSULT,$employee->id);
    }

    /**
     * 获取项目对接订单详情
     * @param string $id
     * @return mixed
     */
    public function getEnterpriseInfo(string $id)
    {
        if (!$list = EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('查询不到该条数据！');
            return false;
        }
        $list['type_name']         =   EnterEnum::getType($list['service_type']);
        $list['status_name']       =   EnterEnum::getStatus($list['status']);
        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =   date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =   date('Y-m-d H:m:s',$list['updated_at']);

        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 添加项目订单信息 前端
     * @param array $data
     * @return mixed
     */
    public function addEnterprise(array $data)
    {
        $member   = $this->auth->user();
        $remark   = $data['remark'] ?? null;
        $status   = EnterEnum::SUBMIT;
        $add_arr = [
            'user_id'           => $member->id,
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'enterprise_name'   => $data['enterprise_name'],
            'service_type'      => $data['service_type'],
            'remark'            => $remark,
            'status'            => $status,
            'reservation_at'    => strtotime($data['reservation_at']),
        ];
        if (EnterpriseOrderRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        $add_arr['created_at'] = time();
        DB::beginTransaction();
        if (!$id = EnterpriseOrderRepository::getAddId($add_arr)){
            $this->setError('预约失败,请重试！');
            DB::rollBack();
            return false;
        }
        $start_process_result = $this->addNewProcessRecord($id,ProcessCategoryEnum::ENTERPRISE_CONSULT);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 修改项目订单信息 后端修改
     * @param array $data
     * @return mixed
     */
    public function updOrderEnterprise(array $data)
    {
        $id = $data['id'];

        if (!$enterpriseInfo = EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('没有找到该订单！');
            return false;
        }
        $status                 =  EnterEnum::SUBMIT;
        $upd_arr = [
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'enterprise_name'   => $data['enterprise_name'],
            'service_type'      => $data['service_type'],
            'remark'            => $data['remark'],
            'status'            => $status,
            'updated_at'        => time(),
            'reservation_at'    => strtotime($data['reservation_at']),
        ];

        if (!EnterpriseOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;
    }

    /**
     * 删除审核订单
     * @param string $id
     * @return mixed
     */
    public function delEnterprise(string $id)
    {
        if (!$EnterpriseInfo = EnterpriseOrderRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('没有找到该数据！');
            return false;
        }
        if (!EnterpriseOrderRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }

    /**
     * 审核预约列表状态(oa)
     * @param $id
     * @param $audit
     * @return bool|null
     */
    public function setEnterpriseOrder($id, $audit)
    {
        if (!$orderInfo = EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('无此订单!');
            return false;
        }
        if ($orderInfo['status'] > EnterEnum::SUBMIT){
            $this->setError('状态不能进行二次审核!');
            return false;
        }
        $upd_arr = [
            'status'      => $audit == 1 ? EnterEnum::PASS : EnterEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if (!$updOrder = EnterpriseOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        #通知用户
        $status = $upd_arr['status'];
        if ($member = MemberBaseRepository::getOne(['id' => $orderInfo['user_id']])){
            $member_name = $orderInfo['name'];
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            $sms_template = [
                EnterEnum::PASS   =>
                    MessageEnum::getTemplate(
                        MessageEnum::CONSULTRESERVE,
                        'auditPass',
                        ['member_name' => $member_name,'enterprise_name' => $orderInfo['enterprise_name']]
                    ),
                EnterEnum::NOPASS =>
                    MessageEnum::getTemplate(
                        MessageEnum::CONSULTRESERVE,
                        'auditNoPass',
                        ['member_name' => $member_name,'enterprise_name' => $orderInfo['enterprise_name']]
                    ),
            ];
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
            }
            $title = '企业咨询预约通知';
            #发送站内信
            SendService::sendMessage($orderInfo['user_id'],MessageEnum::CONSULTRESERVE,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 根据ID修改企业咨询订单
     * @param $request
     * @return bool
     */
    public function editEnterprise($request)
    {
        $id = $request['id'];

        if (!EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('没有找到该订单！');
            return false;
        }
        $status                 =  EnterEnum::SUBMIT;
        $upd_arr = [
            'name'              => $request['name'],
            'mobile'            => $request['mobile'],
            'enterprise_name'   => $request['enterprise_name'],
            'service_type'      => $request['service_type'],
            'remark'            => $request['remark'],
            'status'            => $status,
            'reservation_at'    => strtotime($request['reservation_at']),
        ];
        $upd_arr['updated_at']  = time();
        if (!EnterpriseOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;

    }

    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = EnterpriseOrderRepository::count(['deleted_at' => 0]) ?? 0;
        $audit_count    = EnterpriseOrderRepository::count(['deleted_at' => 0,'status' => ['in',[EnterEnum::PASS,EnterEnum::NOPASS]]]) ?? 0;
        $no_audit_count = EnterpriseOrderRepository::count(['deleted_at' => 0,'status' => EnterEnum::SUBMIT]) ?? 0;
        $cancel_count   = 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }

    /**
     * 成员表取消预约贷款
     * @param $request
     * @return bool
     */
    public function cancelEnterprise($request)
    {
        $member = $this->auth->user();
        if (!$prise = EnterpriseOrderRepository::getOne(['id' => $request['id'],'user_id' => $member->id])){
            $this->setError('没有预约信息!');
            return false;
        }
        if ($prise['status'] > EnterEnum::SUBMIT){
            $this->setError('预约已被审核，不能取消哦!');
            return false;
        }
        if (!EnterpriseOrderRepository::getUpdId(['id' => $prise['id']],['status' => EnterEnum::CANCEL])){
            $this->setError('取消预约失败!');
            return false;
        }
        $this->setMessage('取消成功!');
        return true;
    }

    /**
     * 获取申请人ID
     * @param $order_id
     * @return mixed
     */
    public function getCreatedUser($order_id){
        return EnterpriseOrderRepository::getField(['id' => $order_id],'user_id');
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
        $column     = ['id','user_id','name','mobile','enterprise_name'];
        if (!$order_list = EnterpriseOrderRepository::getAssignList($order_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => $value['enterprise_name'],
                'member_id'     => $value['user_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            