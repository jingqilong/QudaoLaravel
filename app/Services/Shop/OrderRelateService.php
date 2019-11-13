<?php
namespace App\Services\Shop;


use App\Enums\OrderEnum;
use App\Repositories\MemberAddressRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Services\Member\AddressService;
use App\Services\Member\OrdersService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            foreach ($score_category as $key => $score_type){
                foreach ($member_score as $item){
                    if ($score_type == $item['scorer_type']){
                        $goods_score_list   = $this->searchArray($goods_list,'score_categories',$score_type);
                        $score_td           = $this->arrayFieldSum($goods_score_list,'score_deduction');
                        $score_deduction[$key] = [
                            #当前积分类别可抵扣积分总和
                            'total_score'   => $score_td,
                            #当前用户可抵扣当前积分类别最大积分
                            'usable_score'  => $item['score'] > $score_td ? $score_td : $item['score'],
                            'score_type'    => $score_type
                        ];
                    }
                }
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
        $score_type         = $request['score_type'] ?? null;
        $total_price        = $submit_order_info['total_price'];
        if (!empty($score_type)){
            if (!$score = $this->searchArray($submit_order_info['score_deduction'],'score_type',$score_type)){
                $this->setError('该订单不支持此类积分抵扣！');
                return false;
            }
            if ($score_deduction > reset($score)['usable_score']){
                $this->setError('抵扣积分超出抵扣积分最大额！');
                return false;
            }
            #如果有积分抵扣，金额 = 总金额 - 抵扣积分
            $total_price = $total_price - $score_deduction;
        }
        #如果选择付费邮寄，金额 = 总金额 + 邮费
        if ($request['express_type'] == 1){
            $total_price = $total_price + $submit_order_info['express_price'];
        }
        $total_price = $total_price * 100;
        #创建订单
        DB::beginTransaction();
        $orderService = new OrdersService();
        if (!$order_no = $orderService->placeOrder($member->m_id,$total_price,OrderEnum::SHOP)){
            $this->setError($orderService->error);
            DB::rollBack();
            return false;
        }
        if (!$order = MemberOrdersRepository::getOne(['order_no' => $order_no])){
            $this->setError('订单创建失败！');
            DB::rollBack();
            return false;
        }
        #添加订单关联信息
        //TODO
        #添加订单商品信息
        $order_relate_add_arr = [
            ''
        ];
        return $submit_order_info;
    }
}
            