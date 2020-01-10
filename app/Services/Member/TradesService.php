<?php
namespace App\Services\Member;


use App\Enums\OrderEnum;
use App\Enums\TradeEnum;
use App\Library\Members\Member;
use App\Library\Time\Time;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberRelationRepository;
use App\Repositories\MemberTradeListViewRepository;
use App\Repositories\MemberTradesRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Repositories\ShopOrderRelateNameViewRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Tolawho\Loggy\Facades\Loggy;

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
     * @param int $status           交易状态，0、待审核，1、成功，2、失败
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
        if(('+' ==  $fund_flow) && ( 1 == $status  )){
            $ret = $this->addRewardScore($order_id, $pay_user_id, $amount);
            if(false === $ret){
                Loggy::write('error', '更新用户积分失败。',$trade_arr );
            }
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
            $res['day'][]               = $date_time;
            $start_time                 = $today['start'] - ($i * 86400);
            $end_time                   = $today['end'] - ($i * 86400);
            if (empty($list)){
                $res['amount']['total'][]    = rand(2990,5999);continue;
            }
            if ($records = $this->searchRangeArray($list,'create_at',[$start_time, $end_time])){
                $amount  = $this->arrayFieldSum($records,'amount');
                $res['amount']['total'][]    = round($amount/100,2).'';
            }else{
                $res['amount']['total'][]    = rand(2990,5999);
            }
        }
        #各支付方式收入
        foreach ($trade_method as $method){
            $method_name  = TradeEnum::$trade_method[$method] ?? '';
            for ($i = $day;$i >= 0;$i--){
                if (empty($list) || !$trade_record = $this->searchArray($list,'trade_method',$method)){
                    $res['amount'][$method_name][]    = rand(2990,5999);continue;
                }
                $start_time   = $today['start'] - ($i * 86400);
                $end_time     = $today['end'] - ($i * 86400);
                if ($records  = $this->searchRangeArray($trade_record,'create_at',[$start_time, $end_time])){
                    $amount   = $this->arrayFieldSum($records,'amount');
                    $res['amount'][$method_name][]    = round($amount/100,2).'';
                }else{
                    $res['amount'][$method_name][]    = rand(2990,5999);
                }
            }
        }
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 获取交易列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getTradeList($request)
    {
        $request_arr = Arr::only($request,['trade_no','keywords','transaction_no','fund_flow','trade_method','status']);
        $page        = $request['page'] ?? 1;
        $page_num    = $request['page_num'] ?? 20;
        $where       = ['id' => ['<>',0]];
        $column      = ['*'];
        foreach ($request_arr as $key => $value) if (is_null($value)) $where[$key] = $value;
        if (!empty($request_arr['keywords'])){
            $keyword = [$request_arr['keywords'] => ['pay_user_name','mobile']];
            if (!$trade_list = MemberTradeListViewRepository::search($keyword,$where,$column,$page,$page_num,'id','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$trade_list = MemberTradeListViewRepository::getList($where,$column,'id','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $trade_list = $this->removePagingField($trade_list);
        if (empty($trade_list['data'])){
            $this->setMessage('暂无数据！');
            return $trade_list;
        }
        $trade_list['data'] = MemberOrdersRepository::bulkHasOneWalk($trade_list['data'], ['from' => 'order_id','to' => 'id'], ['id','order_no'], [],
            function ($src_item,$src_items){
                $src_item['transaction_no'] = is_null($src_item['transaction_no']) ? '' : $src_item['transaction_no'];
                $src_item['order_no'] = $src_items['order_no'] ?? '';
                $src_item['amount']   = sprintf('%.2f',round($src_item['amount'] / 100, 2));;
                return $src_item;
            }
        );
        $trade_list['data'] = ShopOrderRelateNameViewRepository::bulkHasOneWalk($trade_list['data'], ['from' => 'order_id','to' => 'order_id'], ['order_id','name','spec_relate_value'], [],
            function ($src_item,$src_items){
                $src_item['commodity'] = $src_items['name'] ?? '';
                return $src_item;
            }
        );
        $trade_list['data'] = MemberBaseRepository::bulkHasOneWalk($trade_list['data'], ['from' => 'payee_user_id','to' => 'id'], ['id','ch_name'], [],
            function ($src_item,$src_items){
                $src_item['payee_user_name']    = $src_items['ch_name'] ?? '';
                $src_item['fund_flow_title']    = $src_item['fund_flow'] == '+' ? '付款' : '退款';
                $src_item['order_type_title']   = OrderEnum::getOrderType($src_item['order_type']);
                $src_item['trade_method_title'] = TradeEnum::getTradeMethod($src_item['trade_method']);
                $src_item['status_title']       = TradeEnum::getStatus($src_item['status']);
                $src_item['create_at']          = date('Y-m-d H:i:s',$src_item['create_at']);
                $src_item['end_at']             = date('Y-m-d H:i:s',$src_item['end_at']);
                return $src_item;
            }
        );
        $this->setMessage('获取成功！');
        return $trade_list;
    }

    /**
     * @desc 同步更新推荐人积分
     * 调用条件：付款人是成员，是付款，不是退款，（因为目前没有退款）
     * @param int $order_id         订单id
     * @param int $pay_user_id      付款方ID
     * @param int $amount           交易金额
     * @return bool|mixed
     */
    public function addRewardScore($order_id, $pay_user_id, $amount){
        //从会员订单表获取订单类型
        $order = MemberOrdersRepository::getOne(['id' => $order_id],['user_id','order_type','payment_amount']);
        if(($order['user_id'] != $pay_user_id ) || ($order['payment_amount'] != $amount )){
            Loggy::write('error',"支付人与金额可能不一致！", ['order'=>$order, 'trade'=> [ 'user_id'=>$pay_user_id,'amount'=>$amount]]);
        }
        $order_type = $order['$order'];
        $relation = MemberRelationRepository::getOne(['member_id' => $pay_user_id],['member_id','parent_id','path']);
        if(!$relation){
            Loggy::write('error','推荐关系不存在！', ['member_id'=>$pay_user_id]);
            return false;
        }
        $referrer_user_id = $relation['parent_id'];
        if(0 ==  $referrer_user_id){
            Loggy::write('error','推荐人ID=0！', ['member_id'=>$pay_user_id]);
            return false;
        }
        $referrer_info = MemberGradeRepository::getOne(['user_id'=>$referrer_user_id],['user_id','grade']);
        $member_grade = $referrer_info['grade'];

        //调用发送积分策略
        return  Member::addRewardScore($member_grade,$referrer_user_id,$order_type,$amount);

    }
}
            