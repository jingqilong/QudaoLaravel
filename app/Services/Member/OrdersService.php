<?php
namespace App\Services\Member;


use App\Enums\OrderEnum;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberOrdersViewRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tolawho\Loggy\Facades\Loggy;

class OrdersService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }


    /**
     * 创建订单
     * @param $member_id
     * @param $amount
     * @param $order_type
     * @return bool|null
     */
    public function placeOrder($member_id,$amount, $order_type)
    {
        if (Cache::get(md5($member_id.$order_type.$amount))){
            $this->setError('请勿重复提交！');
            return false;
        }
        if (!$order_id = MemberOrdersRepository::addOrder($amount, $amount, $member_id,$order_type)){
            Loggy::write('order','订单创建失败！用户id：'.$member_id.'  金额：'.$amount.'， 订单类型：');
            $this->setError('订单创建失败！');
            return false;
        }
        if (!$order_no = MemberOrdersRepository::getField([ 'id' => $order_id],'order_no')){
            Loggy::write('order','订单号获取失败！用户id：'.$member_id.'  金额：'.$amount.'， 订单类型：');
            $this->setError('订单号获取失败！');
            return false;
        }
        $this->setMessage('下单成功！');
        return $order_no;
    }

    /**
     * 获取会员订单列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getOrderList($request)
    {
        $order_no   = $request['order_no'] ?? null;
        $member_id  = $request['member_id'] ?? null;
        $order_type = $request['order_type'] ?? null;
        $score_type = $request['score_type'] ?? null;
        $status     = $request['status'] ?? null;
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['id' => ['<>',0]];
        if (!is_null($order_no)){
            $where['order_no'] = $order_no;
        }
        if (!is_null($member_id)){
            $where['user_id'] = $member_id;
        }
        if (!is_null($order_type)){
            $where['order_type'] = $order_type;
        }
        if (!is_null($score_type)){
            $where['score_type'] = $score_type;
        }
        if (!is_null($status)){
            $where['status'] = $status;
        }
        $column = ['*'];
        if (!$order_list = MemberOrdersViewRepository::getList($where,$column,'create_at','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $order_list = $this->removePagingField($order_list);
        if (empty($order_list['data'])){
            $this->setMessage('暂无数据！');
            return $order_list;
        }
        $trade_ids = array_column($order_list['data'],'trade_id');
        $trade_list= MemberTradesRepository::getAssignList($trade_ids);
        foreach ($order_list['data'] as &$order){
            $order['trade_no'] = '';
            if ($trade = $this->searchArray($trade_list,'id',$order['trade_id'])){
                $order['trade_no'] = reset($trade)['trade_no'];
            }
            $order['order_type_title'] = OrderEnum::getOrderType($order['order_type']);
            $order['status_title'] = OrderEnum::getStatus($order['status']);
        }

        $this->setMessage('获取成功！');
        return $order_list;
    }
}
            