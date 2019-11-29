<?php
namespace App\Services\Member;


use App\Enums\OrderEnum;
use App\Enums\TradeEnum;
use App\Library\Time\Time;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class TradesService extends BaseService
{
    use HelpTrait;
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

    /**
     * 获取收益曲线（OA后台首页使用）
     * @param $day
     * @return array|bool
     */
    public function getRevenueRecord($day)
    {
        if ($day < 1){
            $this->setError('查看天数不能低于1天');
            return false;
        }
        $trade_method   = [TradeEnum::WECHANT,TradeEnum::UNION];
        $res            = [];
        $today          = Time::getStartStopTime('today');
        $where          = [
            'status'        => TradeEnum::STATUSSUCCESS,
            'fund_flow'     => '+',
            'trade_method'  => ['<>',TradeEnum::SCORE],
            'create_at'     => ['range',[$today['start'] - ($day * 86400),$today['end'] + ($day * 86400)]]
        ];
        $list = MemberTradesRepository::getList($where,['amount','trade_method','create_at']) ?? [];
        #总收入
        for ($i = $day;$i >= 0;$i--){
            $date_time                  = date('Y-m-d',strtotime('-'.$i.' day'));
            $res['总收入']['day'][]     = $date_time;
            $start_time                 = $today['start'] - ($i * 86400);
            $end_time                   = $today['end'] - ($i * 86400);
            if ($records = $this->searchRangeArray($list,'create_at',[$start_time, $end_time])){
                $amount  = $this->arrayFieldSum($records,'amount');
                $res['总收入']['amount'][]    = round($amount/100,2);
            }else{
                $res['总收入']['amount'][]    = 0;
            }
        }
        #各支付方式收入
        foreach ($trade_method as $method){
            $method_name  = TradeEnum::getTradeMethod($method);
            for ($i = $day;$i >= 0;$i--){
                $date_time                  = date('Y-m-d',strtotime('-'.$i.' day'));
                $res[$method_name]['day'][] = $date_time;
                if ($trade_record = $this->searchArray($list,'trade_method',$method)){
                    $start_time   = $today['start'] - ($i * 86400);
                    $end_time     = $today['end'] - ($i * 86400);
                    if ($records  = $this->searchRangeArray($trade_record,'create_at',[$start_time, $end_time])){
                        $amount   = $this->arrayFieldSum($records,'amount');
                        $res[$method_name]['amount'][]    = round($amount/100,2);
                    }else{
                        $res[$method_name]['amount'][]    = 0;
                    }
                }else{
                    $res[$method_name]['amount'][]    = 0;
                }
            }
        }
        $this->setMessage('获取成功！');
        return $res;
    }
}
            