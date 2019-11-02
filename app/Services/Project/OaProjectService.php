<?php
namespace App\Services\Project;


use App\Enums\ProjectEnum;
use App\Repositories\OaProjectOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
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
        if (!OaProjectOrderRepository::exists(['id' => $id])){
            $this->setError('无此订单!');
            return false;
        }
        $upd_arr = [
            'status'      => $data['status'] == 1 ? ProjectEnum::PASS : ProjectEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if ($updOrder = OaProjectOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            if ($data['status'] == ProjectEnum::PASS){
                //TODO 此处可以添加报名后发通知的事务
                #发送短信
                if (!empty($orderInfo)){
                    $sms = new SmsService();
                    $content = '您好！您预约的《'.$orderInfo['project_name'].'》项目,已通过审核,我们将在24小时内负责人联系您，请保持消息畅通，谢谢！';
                    $sms->sendContent($orderInfo['mobile'],$content);
                }
                $this->setMessage('审核通过,消息已发送给联系人！');
                return $updOrder;
            }
            $this->setMessage('审核成功！');
            return $updOrder;
        }
        $this->setError('审核失败，请重试!');
        return false;
    }
}
            