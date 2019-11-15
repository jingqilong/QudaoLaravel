<?php
namespace App\Services\Member;


use App\Enums\OrderEnum;
use App\Enums\TradeEnum;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\BaseService;

class TradesService extends BaseService
{
    /**
     * 添加交易记录并更新订单
     * @param int $order_id         订单id
     * @param int $pay_user_id      付款方ID
     * @param int $payee_user_id    收款方ID
     * @param int $amount           交易金额
     * @param string $fund_flow     资金流向，+ or -
     * @param int $trade_method     交易方式：1微信支付，2，积分支付，3，银联支付
     * @param int $status           交易状态，0、正在交易，1、成功，2、失败
     * @return bool
     */
    public function tradesUpdOrder($order_id, $pay_user_id, $payee_user_id, $amount, $fund_flow, $trade_method, $status){
        $trade_arr = [
            'order_id'      => $order_id,
            'pay_user_id'   => $pay_user_id,
            'payee_user_id' => $payee_user_id,
            'amount'        => $amount,
            'pay_method'    => $trade_method,
        ];
        if (!$trade_id = MemberTradesRepository::addTrade($trade_arr,$fund_flow,$status)){
            $this->setError('添加交易记录失败！');
            return false;
        }
        $order_upd = ['trade_id' => $trade_id,'updated_at' => time()];
        if ($status == TradeEnum::STATUSSUCCESS){
            $order_upd['status'] = OrderEnum::STATUSSUCCESS;
        }
        if (!MemberOrdersRepository::getUpdId(['id' => $order_id],$order_upd)){
            $this->setError('更新订单信息失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }
}
            