<?php
namespace App\Services\Member;


use App\Repositories\MemberOrdersRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tolawho\Loggy\Facades\Loggy;

class OrdersService extends BaseService
{
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
     * @param $amount
     * @param $order_type
     * @return array|string
     */
    public function placeOrder($amount, $order_type)
    {
        $user = $this->auth->user();
        if (Cache::get(md5($user->m_id.$order_type.$amount))){
            return '请勿重复提交！';
        }
        if (!$order_id = MemberOrdersRepository::addOrder($amount, $amount, $user->m_id,$order_type)){
            Loggy::write('order','订单创建失败！用户id：'.$user->m_id.'  金额：'.$amount.'， 订单类型：');
            return '订单创建失败！';
        }
        if (!$order_no = MemberOrdersRepository::getField([ 'id' => $order_id],'order_no')){
            Loggy::write('order','订单号获取失败！用户id：'.$user->m_id.'  金额：'.$amount.'， 订单类型：');
            return '订单号获取失败！';
        }
        return ['order_no' => $order_no];
    }
}
            