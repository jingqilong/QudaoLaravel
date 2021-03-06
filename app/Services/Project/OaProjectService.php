<?php
namespace App\Services\Project;


use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\ProcessCategoryEnum;
use App\Enums\ProjectEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaProjectOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OaProjectService extends BaseService
{
    use HelpTrait,BusinessTrait;

    protected $auth;


    /**
     * OaProjectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }


    /**
     * @param array $data
     * @return array|bool|null
     */
    public function getProjectOrderList(array $data)
    {
        $employee = Auth::guard('oa_api')->user();
        $asc = $data['asc'] ?? 1;
        $asc = ($asc == 1) ? 'asc' : 'desc';

        $keywords    =   $data['keywords'] ?? null;
        $status      =   $data['status'] ?? null;
        $column      =   ['*'];
        $where       =   ['deleted_at' => 0];

        if ($status !== null){
            $where['status']  = $status;
        }
        if (!empty($keywords)){
            $keyword     =   [$keywords => ['name','mobile','project_name']];
            if (!$list = OaProjectOrderRepository::search($keyword,$where,$column,'id',$asc)) {
                $this->setError('获取失败！');
                return false;
            }
        }else{
                if (!$list = OaProjectOrderRepository::getList($where,$column,'id',$asc)){
                    $this->setError('获取失败！');
                    return false;
                }
            }
        $this->removePagingField($list);
        if (empty($list['data'])) {
            $this->setMessage('没有数据!');
            return $list;
        }
        foreach ($list['data'] as &$value)
        {
            $value['status_name']       =   ProjectEnum::getStatus($value['status']);
            $value['reservation_at']    =   empty($value['reservation_at']) ? '' : date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   empty($value['created_at']) ? '' : date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   empty($value['updated_at']) ? '' : date('Y-m-d H:m:s',$value['updated_at']);
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::PROJECT_DOCKING,$employee->id);
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 根据ID获取订单详细信息
     * @param $id
     * @return bool|mixed|null
     */
    public function getProjectOrderById($id)
    {
        $employee = Auth::guard('oa_api')->user();
        if (!$info = OaProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('没有查到该订单信息!');
            return false;
        }
        $info['status']            =   ProjectEnum::getStatus($info['status']);
        $info['reservation_at']    =   empty($info['reservation_at']) ? '' : date('Y-m-d H:m:s',$info['reservation_at']);
        $info['created_at']        =   empty($info['created_at']) ? '' : date('Y-m-d H:m:s',$info['created_at']);
        $info['updated_at']        =   empty($info['updated_at']) ? '' : date('Y-m-d H:m:s',$info['updated_at']);
        unset($info['deleted_at']);
        return $this->getBusinessDetailsProcess($info,ProcessCategoryEnum::PROJECT_DOCKING,$employee->id);
    }

    /**
     * 审核订单状态
     * @param $id
     * @param $audit
     * @return bool|null
     */
    public function setProjectOrderStatusById($id,$audit)
    {
        if (!$order_info = OaProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('无此订单!');
            return false;
        }
        $upd_arr = [
            'status'      => $audit == 1 ? ProjectEnum::PASS : ProjectEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if (!$updOrder = OaProjectOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        $status = $upd_arr['status'];
        #通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $order_info['user_id']])){
            $member_name = $order_info['name'];
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            $sms_template = [
                ProjectEnum::PASS         =>
                    MessageEnum::getTemplate(
                        MessageEnum::PROJECTBOOKING,
                        'auditPass',
                        ['member_name' => $member_name,'project_name' => $order_info['project_name']]
                    ),
                ProjectEnum::NOPASS       =>
                    MessageEnum::getTemplate(
                        MessageEnum::PROJECTBOOKING,
                        'auditNoPass',
                        ['member_name' => $member_name,'project_name' => $order_info['project_name']]
                    ),
            ];
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
            }
            $title = '项目对接预约通知';
            #发送站内信
            SendService::sendMessage($order_info['user_id'],MessageEnum::PROJECTBOOKING,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return $updOrder;
    }

    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = OaProjectOrderRepository::count(['deleted_at' => 0]) ?? 0;
        $audit_count    = OaProjectOrderRepository::count(['deleted_at' => 0,'status' => ['in',[ProjectEnum::PASS,ProjectEnum::NOPASS]]]) ?? 0;
        $no_audit_count = OaProjectOrderRepository::count(['deleted_at' => 0,'status' => ProjectEnum::SUBMIT]) ?? 0;
        $cancel_count   = 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }
}
            