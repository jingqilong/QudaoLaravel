<?php
namespace App\Services\Enterprise;



use App\Enums\EnterEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\EnterpriseOrderRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OrderService extends BaseService
{

    use HelpTrait;
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
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $type           = $data['type'] ?? null;
        $status         = $data['status'] ?? null;
        $where          = ['deleted_at' => 0];

        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at','deleted_at'];
        if ($type !== null){
            $where['service_type'] = $type;
        }
        if ($status !== null){
            $where['status'] = $status;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['enterprise_name']];
            if (!$list = EnterpriseOrderRepository::search($keyword,$where,$column,$page,$page_num,'created_at','desc')){
                $this->setMessage('没有数据！');
                return [];
            }
        }else{
            if (!$list = EnterpriseOrderRepository::getList($where,$column,'created_at','desc',$page,$page_num)){
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
        }
        $this->setMessage('查找成功');
        return $list;
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
        if (!$res = EnterpriseOrderRepository::getAddId($add_arr)){
            $this->setError('预约失败,请重试！');
            return false;
        }
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
     * @param $request
     * @return bool|null
     */
    public function setEnterpriseOrder($request)
    {
        if (!$orderInfo = EnterpriseOrderRepository::getOne(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('无此订单!');
            return false;
        }
        if ($orderInfo['status'] !== EnterEnum::SUBMIT){
            $this->setError('状态不能进行二次审核!');
            return false;
        }
        $upd_arr = [
            'status'      => $request['status'] == 1 ? EnterEnum::PASS : EnterEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if (!$updOrder = EnterpriseOrderRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        #通知用户
        $status = $upd_arr['status'];
        if ($member = MemberBaseRepository::getOne(['id' => $orderInfo['member_id']])){
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
            SendService::sendMessage($orderInfo['member_id'],MessageEnum::CONSULTRESERVE,$title,$sms_template[$status],$request['id']);
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
}
            