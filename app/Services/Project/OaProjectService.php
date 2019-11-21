<?php
namespace App\Services\Project;


use App\Enums\HouseEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\ProjectEnum;
use App\Repositories\MemberRepository;
use App\Repositories\OaProjectOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OaProjectService extends BaseService
{
    use HelpTrait;

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
        if (empty($data['asc'])){
            $data['asc'] = 1;
        }

        $asc         =   $data['asc'] == 1 ? 'asc' : 'desc';
        $page        =   $data['page'] ?? 1;
        $page_num    =   $data['page_num'] ?? 20;
        $keywords    =   $data['keywords'] ?? null;
        $status      =   $data['status'] ?? null;
        $column      =   ['*'];
        $where       =   ['deleted_at' => 0];

        if ($status !== null){
            $where['status']  = $status;
        }
        if (!empty($keywords)){
            $keyword     =   [$keywords => ['name','mobile','project_name']];
            if (!$list = OaProjectOrderRepository::search($keyword,$where,$column,$page,$page_num,'created_at',$asc)) {
                $this->setError('获取失败！');
                return false;
            }
        }else{
                if (!$list = OaProjectOrderRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
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
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
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
        if (!$orderInfo = OaProjectOrderRepository::exists(['id' => $id])){
            $this->setError('无此订单!');
            return false;
        }

        if (!$orderInfo = OaProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('没有查到该订单信息!');
            return false;
        }
        $orderInfo['status_name']       =   ProjectEnum::getStatus($orderInfo['status']);
        $orderInfo['reservation_at']    =   date('Y-m-d H:m:s',$orderInfo['reservation_at']);
        $orderInfo['created_at']        =   date('Y-m-d H:m:s',$orderInfo['created_at']);
        $orderInfo['updated_at']        =   date('Y-m-d H:m:s',$orderInfo['updated_at']);

        $this->setMessage('获取订单成功！');
        return $orderInfo;
    }

    /**
     * 审核订单状态
     * @param array $data
     * @return bool|null
     */
    public function setProjectOrderStatusById(array $data)
    {
        $id = $data['id'];
        if (!$order_info = OaProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('无此订单!');
            return false;
        }
        $upd_arr = [
            'status'      => $data['status'] == 1 ? ProjectEnum::PASS : ProjectEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if (!$updOrder = OaProjectOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('审核失败，请重试!');
            return false;
        }
        $status = $upd_arr['status'];
        #通知用户
        if ($member = MemberRepository::getOne(['m_id' => $order_info['user_id']])){
            $member_name = $order_info['name'];
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
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
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
            }
            $title = '项目对接预约通知';
            #发送站内信
            SendService::sendMessage($order_info['user_id'],MessageEnum::PROJECTBOOKING,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return $updOrder;
    }
}
            