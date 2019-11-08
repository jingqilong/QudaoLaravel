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
}
            