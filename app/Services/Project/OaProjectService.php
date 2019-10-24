<?php
namespace App\Services\Project;


use App\Enums\ProjectEnum;
use App\Repositories\OaProjectOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use Illuminate\Support\Facades\Auth;

class OaProjectService extends BaseService
{

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
        $page        =   $data['page'] ?? 1;
        $page_num    =   $data['page_num'] ?? 20;
        $keywords    =   $data['keywords'] ?? null;
        $column      =   ['field' => '*'];
        $status      =   [
                    ProjectEnum::SUBMITTED,
                    ProjectEnum::INREVIEW,
                    ProjectEnum::PASS,
                    ProjectEnum::FAILURE
                ];
        $where       = ['status' => ['in',$status]];
        $keyword     = [$keywords => ['name','mobile','project_name','status']];

        if (!$list = OaProjectOrderRepository::search($keyword,$where,$column,$page,$page_num,'created_at','desc')){
            $this->setMessage('没有搜索到此类型订单！');
            return [];
        }

        unset($list['first_page_url'], $list['from'],
              $list['last_page_url'],  $list['from'],
              $list['next_page_url'],  $list['path'],
              $list['prev_page_url'],  $list['to']);

        if (empty($list['data'])) {
            $this->setMessage('没有搜索到此类型订单!');
        }

        foreach ($list['data'] as &$value)
        {
            switch ($value['status']) {
                case ProjectEnum::SUBMITTED:
                    $value['status'] = '已提交';
                    break;
                case ProjectEnum::INREVIEW:
                    $value['status'] = '审核中';
                    break;
                case ProjectEnum::PASS:
                    $value['status'] = '审核通过';
                    break;
                case ProjectEnum::FAILURE:
                    $value['status'] = '审核失败';
                    break;
                case ProjectEnum::DELETE:
                    $value['status'] = '已删除';
                    break;
                default ;
            }
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

        if ($orderInfo['status'] == ProjectEnum::DELETE){
            $this->setError('该订单已被删除，如有需求请联系超级管理员！');
            return false;
        }

        if (!empty($orderInfo)) {
            switch ($orderInfo['status']) {
                case ProjectEnum::SUBMITTED:
                    $orderInfo['status'] = '已提交';
                    break;
                case ProjectEnum::INREVIEW:
                    $orderInfo['status'] = '审核中';
                    break;
                case ProjectEnum::PASS:
                    $value['status'] = '审核通过';
                    break;
                case ProjectEnum::FAILURE:
                    $orderInfo['status'] = '审核失败';
                    break;
                case ProjectEnum::DELETE:
                    $orderInfo['status'] = '已删除';
                    break;
                default ;
            }
        }
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
        unset($data['sign'],$data['token'],$data['id']);

        $statusGroup = [2,3,4];
        if (!in_array($data['status'],$statusGroup)){
            $this->setError('审核类型不存在！');
            return false;
        }

        if (!$orderInfo = OaProjectOrderRepository::exists(['id' => $id])){
            $this->setError('无此订单!');
            return false;
        }

        if (!$orderInfo = OaProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('没有查到该订单信息!');
            return false;
        }

        if ($updOrder = OaProjectOrderRepository::getUpdId(['id' => $id],$data)){
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
            