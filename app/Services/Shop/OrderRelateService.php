<?php
namespace App\Services\Shop;


use App\Enums\OrderEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\MemberAddressRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopOrderGoodsRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Services\Member\AddressService;
use App\Services\Member\OrdersService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class OrderRelateService extends BaseService
{
    use HelpTrait;

    /**
     * 获取下单详情
     * @param $request
     * @return mixed
     */
    public function getPlaceOrderDetail($request)
    {
        $goods_param        = json_decode($request['goods_json'],true);
        $goods_id           = array_column($goods_param,'goods_id');
        $goods_list         = ShopGoodsRepository::getList(['id' => ['in',$goods_id]]);
        $buy_score          = 0;
        $score_deduction    = [];
        $express_price      = 0;
        $total_price        = 0;
        $score_category     = array_column($goods_list,'score_categories');
        //TODO 此处对接积分
        $member_score       = [
            [
                'score'             => 2000,
                'scorer_type'       => 1,
                'score_title'       => '通用积分'
            ]
        ];
        foreach ($goods_param as $value){
            if ($goods = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $buy_score          += reset($goods)['gift_score'];
                $express_price      += reset($goods)['express_price'];
                $total_price        += reset($goods)['price'];
            }
            $goods_score   = $this->searchArray($goods_list,'id',$value['goods_id']);
            if (empty(reset($goods)['score_categories'])){
                break;
            }
            $usable_member_score    = $this->searchArray($member_score,'scorer_type',reset($goods_score)['score_categories']);
            $usable_score           = !empty($usable_member_score) ? reset($usable_member_score)['score'] : 0;
            #当前商品的总抵扣积分
            $goods_total_score  = reset($goods_score)['score_deduction'] * $value['number'];
            $goods_score_type   = reset($goods_score)['score_categories'];
            if (!isset($score_deduction[$goods_score_type])){
                #当前积分类别可抵扣积分总和
                $score_deduction[$goods_score_type]['total_score'] = $goods_total_score;
                #当前用户可抵扣当前积分类别最大积分
                $score_deduction[$goods_score_type]['usable_score'] = $usable_score > $score_deduction[$goods_score_type]['total_score'] ? $score_deduction[$goods_score_type]['total_score'] : $usable_score;
                $score_deduction[$goods_score_type]['score_type'] = $goods_score_type;
            }else{
                #当前积分类别可抵扣积分总和
                $score_deduction[$goods_score_type]['total_score'] = $goods_total_score + $score_deduction[$goods_score_type]['total_score'];
                #当前用户可抵扣当前积分类别最大积分
                $score_deduction[$goods_score_type]['usable_score'] = $usable_score > $score_deduction[$goods_score_type]['total_score'] ? $score_deduction[$goods_score_type]['total_score'] : $usable_score;
            }
        }

        $express_title          = empty($express_price) ? '包邮' : '江浙沪地区包邮，其它地区总邮费：' . round($express_price / 100,2).'元';
        $member                 = Auth::guard('member_api')->user();
        #收货地址
        $res['address']         = AddressService::getDefaultAddress($member->m_id);
        #商品信息
        $res['goods_info']      = GoodsSpecRelateService::getListCommonInfo($goods_param);
        #邮费展示标签
        $res['express_title']   = $express_title;
        #邮费
        $res['express_price']   = round($express_price / 100,2);
        #购买所得积分
        $res['buy_score']       = $buy_score;
        #可抵扣积分
        $res['score_deduction'] = $score_deduction;
        #订单总金额
        $res['total_price']     = round($total_price / 100,2);
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 提交订单
     * @param $request
     * @return bool|mixed
     */
    public function submitOrder($request)
    {
        $member = Auth::guard('member_api')->user();
        if (!MemberAddressRepository::exists(['id' => $request['address_id']])){
            $this->setError('收货地址不存在！');
            return false;
        }
        $submit_order_info = $this->getPlaceOrderDetail($request);
        $score_deduction    = $request['score_deduction'] ?? 0;
        $score_type         = $request['score_type'] ?? 0;
        $total_price        = $submit_order_info['total_price'];
        $is_score           = false;#表示是否使用积分，默认不使用
        #如果选择付费邮寄，金额 = 总金额 + 邮费
        if ($request['express_type'] == ShopOrderEnum::BY_MAIL){
            $total_price = $total_price + $submit_order_info['express_price'];
        }
        $payment_price = $total_price;
        DB::beginTransaction();
        if (!empty($score_type)){
            if (!$score = $this->searchArray($submit_order_info['score_deduction'],'score_type',$score_type)){
                $this->setError('该订单不支持此类积分抵扣！');
                DB::rollBack();
                return false;
            }
            if ($score_deduction > reset($score)['usable_score']){
                $this->setError('抵扣积分超出抵扣积分最大额！');
                DB::rollBack();
                return false;
            }
            $is_score   = true;
            #如果有积分抵扣，实际支付金额 = 总金额 - 抵扣积分
            $payment_price = $total_price - $score_deduction;
            //TODO  此处扣除积分
        }
        #将总金额、实际支付金额单位分换算为元
        $total_price    = $total_price * 100;   #总金额
        $payment_price  = $payment_price * 100; #实际支付金额
        #创建订单
        $order_add_arr = [
            'order_no'          => MemberOrdersRepository::getOrderNo(),
            'user_id'           => $member->m_id,
            'order_type'        => OrderEnum::SHOP,
            'amount'            => $total_price,
            'payment_amount'    => $payment_price,
            'score_deduction'   => $is_score ? $score_deduction : 0,
            'score_type'        => $score_type,
            'status'            => ($payment_price === 0) ? OrderEnum::STATUSSUCCESS : OrderEnum::STATUSTRADING,
            'create_at'         => time(),
            'updated_at'        => time(),
        ];
        if (!$order_id = MemberOrdersRepository::getAddId($order_add_arr)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建总订单记录失败！用户ID：'.$member->m_id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单关联信息
        $order_relate_arr = [
            'order_id'          => $order_id,
            'member_id'         => $member->m_id,
            'status'            => $order_add_arr == OrderEnum::STATUSSUCCESS ? ShopOrderEnum::SHIP : ShopOrderEnum::PAYMENT,
            'express_price'     => ($request['express_type'] == 1) ? $submit_order_info['express_price'] * 100 : 0,
            'address_id'        => $request['address_id'],
            'remarks'           => $request['remarks'] ?? '',
            'receive_method'    => $request['express_type'],
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
        if (!$order_relate_id = ShopOrderRelateRepository::getAddId($order_relate_arr)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建订单关联记录失败！用户ID：'.$member->m_id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单商品信息
        $order_relate_add_arr = [];
        foreach ($submit_order_info['goods_info'] as $goods){
            $order_relate_add_arr[] = [
                'order_relate_id'   => $order_relate_id,
                'goods_id'          => $goods['goods_id'],
                'spec_relate_id'    => $goods['spec_relate_id'],
                'number'            => $goods['number'],
                'created_at'        => time(),
            ];
        }
        if (!ShopOrderGoodsRepository::create($order_relate_add_arr)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建订单商品记录失败！用户ID：'.$member->m_id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('下单成功！');
        return [
            'status'    => ( $order_add_arr == OrderEnum::STATUSSUCCESS ? 1 : 2),#此状态1表示不需要支付，2表示需要支付
            'order_no'  => MemberOrdersRepository::getField(['id' => $order_id],'order_no')
        ];
    }

    /**
     * 确认收货
     * @param $order_relate_id
     * @return bool
     */
    public function goodsReceiving($order_relate_id)
    {
        $member = Auth::guard('member_api')->user();
        $where = ['member_id' => $member->m_id,'id' => $order_relate_id,'deleted_at' => 0,'status' => ['<>',ShopOrderEnum::CANCELED]];
        if (!$order_relate = ShopOrderRelateRepository::getOne($where)){
            $this->setError('订单信息不存在！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::PAYMENT){
            $this->setError('您的订单未完成支付，无法完成收货，请先进行支付！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::SHIP){
            $this->setError('您的订单还未发货，无法完成收货！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::RECEIVED){
            $this->setError('您已经确认收货了，不要重复确认收货！');
            return false;
        }
        if (ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::RECEIVED,'updated_at' => time()])){
            $this->setMessage('确认收货成功！');
            return true;
        }
        $this->setError('确认收货失败！');
        return false;
    }

    /**
     * 取消订单
     * @param $order_relate_id
     * @return bool
     */
    public function cancelOrder($order_relate_id)
    {
        $member = Auth::guard('member_api')->user();
        $where = ['member_id' => $member->m_id,'id' => $order_relate_id,'deleted_at' => 0];
        if (!$order_relate = ShopOrderRelateRepository::getOne($where)){
            $this->setError('订单信息不存在！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::CANCELED){
            $this->setError('您的订单已取消！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::SHIP){
            $this->setError('您的订单已支付，无法取消！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::SHIPPED){
            $this->setError('您的订单已发货，无法取消！');
            return false;
        }
        if ($order_relate['status'] == ShopOrderEnum::RECEIVED){
            $this->setError('您的订单已完成，无法取消！');
            return false;
        }
        if (ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::CANCELED,'updated_at' => time()])){
            $this->setMessage('取消订单成功！');
            return true;
        }
        $this->setError('取消订单失败！');
        return false;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getMyOrderList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $status     = $request['status'] ?? null;
        $where      = ['id' => ['<>',0]];
        if (!is_null($status)){
            $where['status']    = $status;
        }
//        if (!){
//            $this->setError('获取失败！');
//            return false;
//        }
    }
}
            