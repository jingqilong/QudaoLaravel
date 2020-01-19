<?php
namespace App\Services\Shop;


use App\Enums\CommonAuditStatusEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\OrderEnum;
use App\Enums\ProcessCategoryEnum;
use App\Enums\ShopGoodsEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\ShopOrderTypeEnum;
use App\Enums\TradeEnum;
use App\Services\BaseService;
use App\Services\Common\ExpressService;
use App\Services\Member\AddressService;
use App\Traits\BusinessTrait;
use App\Repositories\{
    CommonExpressRepository,
    MemberAddressRepository,
    MemberOrdersRepository,
    MemberBaseRepository,
    MemberTradesRepository,
    ScoreCategoryRepository,
    ShopCartRepository,
    ShopGoodsRepository,
    ShopGoodsSpecRelateRepository,
    ShopGoodsSpecRepository,
    ShopOrderGoodsRepository,
    ShopOrderRelateRepository,
    ShopOrderRelateViewRepository};
use App\Services\Common\SmsService;
use App\Services\Member\TradesService;
use App\Services\Message\SendService;
use App\Services\Score\RecordService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class OrderRelateService extends BaseService
{
    use HelpTrait,BusinessTrait;

    #包邮地区编码
    public static $free_shipping_area_code = ['330000','320000','310000'];
    /**
     * 获取下单详情
     * @param $request
     * @return mixed
     */
    public function getPlaceOrderDetail($request)
    {
        $member = Auth::guard('member_api')->user();
        $goods_param        = json_decode($request['goods_json'],true);
        $goods_ids          = array_column($goods_param,'goods_id');
        $spec_relate_ids    = array_column($goods_param,'spec_relate_id');
        $goods_list         = ShopGoodsRepository::getAllList(['id' => ['in',$goods_ids]]);
        $goods_list         = createArrayIndex($goods_list,'id');
        $spec_relate_list   = ShopGoodsSpecRelateRepository::getAllList(['id' => ['in',$spec_relate_ids]]);
        $spec_relate_list   = createArrayIndex($spec_relate_list,'id');
        foreach ($goods_list as $value){
            if ($value['negotiable'] == ShopGoodsEnum::NEGOTIABLE){
                $this->setError('商品【'.$value['name'].'】为面议商品！');
                return false;
            }
        }
        #购买所得积分
        $buy_score          = 0;
        #邮费
        $express_price      = 0;
        #订单总金额
        $total_price        = 0;
        $scoreService = new RecordService();
        foreach ($goods_param as $value){
            if(!isset($goods_list[$value['goods_id']]))continue;
            $goods = $goods_list[$value['goods_id']];
            $spec_relate_id     =  $value['spec_relate_id']??0;
            $buy_score          += $goods['gift_score'];
            $express_price      += $goods['express_price'];
            $total_price        += (isset($spec_relate_list[$spec_relate_id]) ? $spec_relate_list[$spec_relate_id]['price'] : $goods['price']) * $value['number'];
        }
        $total_price            = sprintf('%.2f',round($total_price / 100,2));
        #可抵扣积分
        $score_deduction        = $scoreService->getUsableScore($member->id,$goods_param,$goods_list,$total_price);
        $express_title          = empty($express_price) ? '包邮' : '江浙沪地区包邮，其它地区总邮费：' . sprintf('%.2f',round($express_price / 100,2)).'元';
        $member                 = Auth::guard('member_api')->user();
        #收货地址
        $res['address']         = AddressService::getDefaultAddress($member->id);
        #商品信息
        $res['goods_info']      = GoodsSpecRelateService::getListCommonInfo($goods_param);
        #邮费展示标签
        $res['express_title']   = $express_title;
        #邮费
        $res['express_price']   = sprintf('%.2f',round($express_price / 100,2));
        #购买所得积分
        $res['buy_score']       = $buy_score;
        #可抵扣积分
        $res['score_deduction'] = $score_deduction;
        #订单总金额
        $res['total_price']     = $total_price;
        $this->setMessage('获取成功！');
        return $res;
    }
    /**
     * 获取下单详情
     * @param $request
     * @return mixed
     */
    public function getNegotiablePlaceOrderDetail($request)
    {
        #检查是否存在非面议商品
        $goods_param        = json_decode($request['goods_json'],true);
        $goods_ids          = array_column($goods_param,'goods_id');
        $goods_list         = ShopGoodsRepository::getAllList(['id' => ['in',$goods_ids]]);
        $goods_list         = createArrayIndex($goods_list,'id');
        foreach ($goods_list as $value){
            if ($value['negotiable'] !== ShopGoodsEnum::NEGOTIABLE){
                $this->setError('商品【'.$value['name'].'】为非面议商品！');
                return false;
            }
        }
        #购买所得积分
        $buy_score          = 0;
        #邮费
        $express_price      = 0;
        foreach ($goods_param as $value){
            if (isset($goods_list[$value['goods_id']])){
                $goods          =  $goods_list[$value['goods_id']];
                $buy_score      += $goods['gift_score'];
                $express_price  += $goods['express_price'];
            }
        }
        $express_title          = empty($express_price) ? '包邮' : '江浙沪地区包邮，其它地区总邮费：' . sprintf('%.2f',round($express_price / 100,2)).'元';
        $member                 = Auth::guard('member_api')->user();
        #收货地址
        $res['address']         = AddressService::getDefaultAddress($member->id);
        #商品信息
        $res['goods_info']      = GoodsSpecRelateService::getNegotiableGoodsInfo($goods_param);
        #邮费展示标签
        $res['express_title']   = $express_title;
        #邮费
        $res['express_price']   = sprintf('%.2f',round($express_price / 100,2));
        #购买所得积分
        $res['buy_score']       = $buy_score;
        #订单总金额
        $res['total_price']     = '面议';
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
        if (Cache::has($request['token'])){
            $this->setError('请勿重复提交！');
            return false;
        }
        $member = Auth::guard('member_api')->user();
        if (!$address = MemberAddressRepository::getOne(['id' => $request['address_id']])){
            $this->setError('收货地址不存在！');
            return false;
        }
        $goods_json = json_decode($request['goods_json'],true);
        #检查库存
        $goodsSpecRelateService = new GoodsSpecRelateService();
        if (!$goodsSpecRelateService->checkStock($goods_json)){
            $this->setError($goodsSpecRelateService->error);
            return false;
        }
        $submit_order_info  = $this->getPlaceOrderDetail($request);
        $express_price      = $submit_order_info['express_price'];
        $score_deduction    = $request['score_deduction'] ?? 0;
        $score_type         = $request['score_type'] ?? 0;
        $total_price        = $submit_order_info['total_price'];
        $scoreService       = new RecordService();
        $is_score           = false;#表示是否使用积分，默认不使用
        #如果选择付费邮寄，金额 = 总金额 + 邮费
        if ($request['express_type'] == ShopOrderEnum::BY_MAIL){
            $area_code = explode(',',trim($address['area_code'],','));
            if (array_intersect($area_code,self::$free_shipping_area_code)){
                $express_price = 0;
            }
            $total_price = $total_price + $express_price;
        }
        $payment_price  = $total_price;
        $trade_score    = 0;
        DB::beginTransaction();
        if (!empty($score_type)){
            if (!$score = $this->searchArray($submit_order_info['score_deduction'],'score_type',$score_type)){
                $this->setError('该订单不支持此类积分抵扣！');
                DB::rollBack();
                return false;
            }
            $score = reset($score);
            if ($score_deduction > $score['usable_score']){
                $this->setError('抵扣积分超出抵扣积分最大额！');
                DB::rollBack();
                return false;
            }
            $is_score   = true;
            #如果有积分抵扣，实际支付金额 = 总金额 - (抵扣积分 * 抵扣率)
            $payment_price = $total_price - ($score_deduction * $score['expense_rate']);
            $trade_score   = $score_deduction;
            //扣除积分
            if (!$scoreService->expenseScore($score_type,$score_deduction,$member->id,'商品抵扣')){
                $this->setError('积分抵扣失败！');
                DB::rollBack();
                return false;
            }
        }
        #将总金额、实际支付金额单位元换算为分
        $total_price    = $total_price * 100;   #总金额
        $payment_price  = $payment_price * 100; #实际支付金额
        #创建订单
        $order_add_arr = [
            'order_no'          => MemberOrdersRepository::getOrderNo(),
            'user_id'           => $member->id,
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
            Loggy::write('order','创建总订单记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #创建交易记录
        $TradesService = new TradesService();
        $trade_amount = $payment_price;
        if ($payment_price === 0){//如果支付金额为0，则交易金额为积分金额
            $trade_amount = $trade_score;
        }
        $trade_method = 0;
        if ($trade_amount === 0 && $trade_score !== 0){
            $trade_method = TradeEnum::SCORE;
        }
        $trade_status = $order_add_arr['status'] == OrderEnum::STATUSSUCCESS ? TradeEnum::STATUSSUCCESS : TradeEnum::STATUSTRADING;
        if (!$TradesService->tradesUpdOrder($order_id,$member->id,0,$trade_amount,'+',$trade_method,$trade_status)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建交易记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单关联信息
        $order_relate_arr = [
            'order_id'          => $order_id,
            'member_id'         => $member->id,
            'status'            => $order_add_arr == OrderEnum::STATUSSUCCESS ? ShopOrderEnum::SHIP : ShopOrderEnum::PAYMENT,
            'audit'             => CommonAuditStatusEnum::PASS,
            'order_type'        => ShopOrderTypeEnum::ORDINARY,
            'express_price'     => ($request['express_type'] == 1) ? $express_price * 100 : 0,
            'address_id'        => $request['address_id'],
            'income_score'      => $submit_order_info['buy_score'],#此处赠送积分待支付完成赠送
            'remarks'           => $request['remarks'] ?? '',
            'receive_method'    => $request['express_type'],
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
        if (!$order_relate_id = ShopOrderRelateRepository::getAddId($order_relate_arr)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建订单关联记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单商品信息
        if (!ShopOrderGoodsRepository::addOrderGoods($submit_order_info['goods_info'],$order_relate_id)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建订单商品记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #锁定库存
        $shopInventorService = new ShopInventorService();
        foreach ($goods_json as $value){
            if (!$shopInventorService->lockStock($value['goods_id'],$value['spec_relate_id'] ?? 0,$value['number'])){
                $this->setError('锁定库存失败！');
                DB::rollBack();
                return false;
            }
        }
        //如果是购物车下单，下单完成后删除购物车记录
        $car_ids = $request['car_ids'] ?? null;
        if (!is_null($car_ids)){
            ShopCartRepository::delete(['id' => ['in',explode(',',$car_ids)]]);
        }
        DB::commit();
        Cache::put($request['token'],$request['token'],5);
        $this->setMessage('下单成功！');
        return [
            'order_relate_id' => $order_relate_id,
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
        $where = ['member_id' => $member->id,'id' => $order_relate_id,'deleted_at' => 0,'status' => ['<>',ShopOrderEnum::CANCELED]];
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
        if (ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::RECEIVED,'receive_at' => time(),'updated_at' => time()])){
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
        $where = ['member_id' => $member->id,'id' => $order_relate_id,'deleted_at' => 0];
        if (!$order_relate = ShopOrderRelateViewRepository::getOne($where)){
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
        return $this->offOrder($order_relate);
    }

    /**
     * 取消订单主方法
     * @param $order_relate
     * @return bool
     */
    public function offOrder($order_relate){
        DB::beginTransaction();
        #更新订单关联表信息
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate['id']],['status' => ShopOrderEnum::CANCELED,'updated_at' => time()])){
            $this->setError('取消订单失败！');
            DB::rollBack();
            return false;
        }
        #更新订单总表信息
        if (!MemberOrdersRepository::getUpdId(['id' => $order_relate['order_id']],['status' => OrderEnum::STATUSCLOSE,'updated_at' => time()])){
            $this->setError('取消订单失败！');
            DB::rollBack();
            return false;
        }
        #更新交易表信息
        if (!empty($order_relate['trade_id']))
        if (!MemberTradesRepository::getUpdId(['id' => $order_relate['trade_id']],['status' => TradeEnum::STATUSFAIL])){
            $this->setError('取消订单失败！');
            DB::rollBack();
            return false;
        }
        #归还库存
        $order_goods_list = ShopOrderGoodsRepository::getAllList(['order_relate_id' => $order_relate['id']]);
        $shopInventorService = new ShopInventorService();
        foreach ($order_goods_list as $value){
            if (!$shopInventorService->unlockStock($value['goods_id'],$value['spec_relate_id'] ?? 0,$value['number'])){
                $this->setError('库存退还失败！');
                DB::rollBack();
                return false;
            }
        }
        //退还积分
        if (!empty($order_relate['score_type'])){
            $scoreService = new RecordService();
            if (!$scoreService->increaseScore($order_relate['score_type'],$order_relate['score_deduction'],$order_relate['member_id'],'商品抵扣积分退还','取消订单退还')){
                $this->setError('积分退还失败！');
                DB::rollBack();
                return false;
            }
        }
        #面议订单需要结束流程
        if ($order_relate['order_relate_type'] == ShopOrderTypeEnum::NEGOTIABLE){
            if (!$this->cancelBusinessProcess($order_relate['id'],ProcessCategoryEnum::SHOP_NEGOTIABLE_ORDER)){
                $this->setError('取消订单失败！');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('取消订单成功！');
        DB::commit();
        return true;
    }

    /**
     * 用户获取自己的订单列表
     * @param $request
     * @return mixed
     */
    public function getMyOrderList($request)
    {
        $member     = Auth::guard('member_api')->user();
        $status     = $request['status'] ?? null;
        $where      = ['member_id' => $member->id,'deleted_at' => 0];
        if (!is_null($status)){
            $where['status']    = $status;
        }
        $column = ['id','status','payment_amount','income_score','express_number','express_company_id','order_no','order_relate_type','audit'];
        #获取订单列表
        if (!$order_list = ShopOrderRelateViewRepository::getList($where,$column,'id','desc')){
            $this->setError('获取失败！');
            return false;
        }
        $order_list = $this->removePagingField($order_list);
        if (empty($order_list['data'])){
            $this->setMessage('您还没有订单，快去购买吧！');
            return $order_list;
        }
        #获取物流公司列表
        $express_company_ids   = array_column($order_list['data'],'express_company_id');
        $express_company_list  = CommonExpressRepository::getAllList(['id' => ['in',$express_company_ids]]);
        #获取订单中的商品列表
        $order_relate_ids   = array_column($order_list['data'],'id');
        $order_goods_list   = ShopOrderGoodsRepository::getAllList(['order_relate_id' => ['in',$order_relate_ids]]);
        $goods_list         = GoodsSpecRelateService::getListCommonInfo($order_goods_list);
        foreach ($order_list['data'] as &$value){
            $value['is_comment'] = $value['status'] == ShopOrderEnum::FINISHED ? 1 : 0;
            $value['payment_amount'] = sprintf('%.2f',round($value['payment_amount'] / 100,2));
            if ($search_goods_list = $this->searchArray($goods_list,'order_relate_id',$value['id'])){
                $value['goods_list'] = $search_goods_list;
            }
            $value['company_code'] = '';
            $value['company_name'] = '';
            if (!empty($value['express_company_id'])){
                $express_company = $this->searchArray($express_company_list,'id',$value['express_company_id']);
                $value['company_name'] = empty($express_company) ? '' : reset($express_company)['company_name'];
                $value['company_code'] = empty($express_company) ? '' : reset($express_company)['code'];
            }
            $value['status_title'] = ShopOrderEnum::getStatus($value['status']);
            if ($value['order_relate_type'] == ShopOrderTypeEnum::NEGOTIABLE && $value['audit'] !== CommonAuditStatusEnum::PASS){
                $value['payment_amount'] = '面议';
            }
        }
        $this->setMessage('获取成功！');
        return $order_list;
    }

    /**
     * 获取订单详情
     * @param $order_relate_id
     * @param null $member_id
     * @return mixed
     */
    public function orderDetail($order_relate_id, $member_id = null)
    {
        $where  = ['id' => $order_relate_id];
        if (!empty($member_id)){
            $where['member_id'] = $member_id;
        }
        $column = ['id','status','express_company_id','express_price','express_number','address_id','remarks','receive_method','order_no','trade_id','amount','payment_amount','score_deduction','score_type','receive_name','receive_mobile','receive_area_code','receive_address','shipment_at','order_relate_type','receive_at','created_at'];
        if (!$order = ShopOrderRelateViewRepository::getOne($where,$column)){
            $this->setError('订单不存在！');
            return false;
        }
        $order['score_type']    = empty($order['score_type']) ? '' : ScoreCategoryRepository::getField(['id' => $order['score_type']],'name');
        $order['status_title']  = ShopOrderEnum::getStatus($order['status']);
        $order['receive_method']= ShopOrderEnum::getReceiveMethod($order['receive_method']);
        $order['express_price'] = round($order['express_price'] / 100,2);
        $order['express_number']= $order['express_number'] ?? '';
        $order['amount']        = sprintf('%.2f',round($order['amount'] / 100,2));
        $order['payment_amount']= sprintf('%.2f',round($order['payment_amount'] / 100,2));
        $order['shipment_at']   = empty($order['shipment_at']) ? 0 : date('Y-m-d H:i:s',$order['shipment_at']);
        $order['receive_at']    = empty($order['receive_at']) ? 0 : date('Y-m-d H:i:s',$order['receive_at']);
        $order['trade_no']          = '';//交易号
        $order['transaction_no']    = '';//第三方交易号
        $order['trade_method']      = '';//交易方式
        $order['pay_at']            = '';//支付时间
        if (!empty($order['trade_id'])){
            if ($trade = MemberTradesRepository::getOne(['id' => $order['trade_id']])){
                $order['trade_no']          = $trade['trade_no'] ?? '';
                $order['transaction_no']    = $trade['transaction_no'] ?? '';
                $order['trade_method']      = TradeEnum::getTradeMethod($trade['trade_method']);
                $order['pay_at']            = empty($trade['end_at']) ? '' : date('Y-m-d H:i:s',$trade['end_at']);
            }
        }
        $order['express_company_code'] = '';
        if (!empty($order['express_company_id'])){
            $order['express_company_code'] = CommonExpressRepository::getField(['id' => $order['express_company_id']],'code');
        }
        list($order['receive_area_address'])  = $this->makeAddress($order['receive_area_code'],$order['receive_address']);
        $order_goods_list       = ShopOrderGoodsRepository::getAllList(['order_relate_id' => $order['id']]);
        $order['goods_list']    = GoodsSpecRelateService::getListCommonInfo($order_goods_list);
        unset($order['receive_area_code'],$order['receive_address'],$order['express_company_id']);
        $this->setMessage('获取成功！');
        return $order;
    }

    /**
     * 用户根据订单号获取物流状态
     * @param $code
     * @param $number
     * @return bool
     * @deprecated true
     */
    public function getOrderExpressDetails($code, $number)
    {
        if (!$expressInfo = CommonExpressRepository::getOne(['code' => $code,'status' => 1])){
            $this->setError('快递公司不存在!');
            return false;
        }
        $expressDetail = ExpressService::getExpressDetails($code, $number);
        if ($expressDetail['message'] != 'ok'){
            $this->setError($expressDetail['message']);
            return false;
        }
        $result['express_name'] = $expressInfo['company_name'];
        $result['express_number'] = $expressDetail['nu'];
        $result['ischeck'] = $expressDetail['ischeck'] == 0 ? '未签收' : '已签收';
        $result['data'] = $expressDetail['data'];
        $this->setMessage('获取成功!');
        return $result;
    }
    /**
     * 后台获取所有商城订单列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getShopOrderList($request)
    {
        $keywords       = $request['keywords'] ?? null;
        $status         = $request['status'] ?? null;
        $order_no       = $request['order_no'] ?? null;
        $express_number = $request['express_number'] ?? null;
        $receive_method = $request['receive_method'] ?? null;
        $express_company_id = $request['express_company_id'] ?? null;
        $order          = 'id';
        $desc_asc       = 'desc';
        $where          = ['id' => ['<>',0],'order_relate_type' => ShopOrderTypeEnum::ORDINARY,'deleted_at' => 0];
        $column         = ['id','status','express_company_id','express_price','express_number','remarks','receive_method','order_no','amount','payment_amount','receive_name','receive_mobile','member_name','member_mobile','created_at'];
        if (!is_null($status)){
            $where['status']  = $status;
        }
        if (!is_null($order_no)){
            $where['order_no']  = $order_no;
        }
        if (!is_null($express_number)){
            $where['express_number']  = $express_number;
        }
        if (!is_null($receive_method)){
            $where['receive_method']  = $receive_method;
        }
        if (!is_null($express_company_id)){
            $where['express_company_id']  = $express_company_id;
        }
        if (!empty($keywords)){
            $keywords_column = [$keywords => ['member_name','member_mobile','receive_name','receive_mobile','remarks']];
            if (!$order_list = ShopOrderRelateViewRepository::search($keywords_column,$where,$column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$order_list = ShopOrderRelateViewRepository::getList($where,$column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $order_list = $this->removePagingField($order_list);
        if (empty($order_list['data'])){
            $this->setMessage('暂无数据！');
            return $order_list;
        }
        $express_company_ids  = array_column($order_list['data'],'express_company_id');
        $express_company_list = CommonExpressRepository::getAssignList($express_company_ids);
        foreach ($order_list['data'] as &$value){
            $value['amount'] = sprintf('%.2f',round($value['amount'] / 100,2));
            $value['payment_amount'] = sprintf('%.2f',round($value['payment_amount'] / 100,2));
            $value['express_price'] = sprintf('%.2f',round($value['express_price'] / 100,2));
            $value['status_title'] = ShopOrderEnum::getStatus($value['status']);
            $value['express_company']   = '-';
            $value['express_company_code']   = '';
            if ($express_company = $this->searchArray($express_company_list,'id',$value['express_company_id'])){
                $value['express_company'] = reset($express_company)['company_name'];
                $value['express_company_code'] = reset($express_company)['code'];
            }
            $value['receive_method']    = ShopOrderEnum::getReceiveMethod($value['receive_method']);
            $value['express_number']    = $value['express_number'] ?? '-';
        }
        $this->setMessage('获取成功！');
        return $order_list;
    }

    /**
     * 发货
     * @param $request
     * @return bool
     */
    public function shipment($request)
    {
        if (!$express_company = CommonExpressRepository::getOne(['id' => $request['express_company_id']])){
            $this->setError('快递公司不存在！');
            return false;
        }
        $where = ['id' => $request['order_relate_id'],'deleted_at' => 0];
        if (!$order_relate = ShopOrderRelateViewRepository::getOne($where)){
            $this->setError('订单不存在！');
            return false;
        }
        if ($order_relate['audit'] !== CommonAuditStatusEnum::PASS){
            $this->setError('该订单未通过审核，不能发货！');
            return false;
        }
        if ($order_relate['status'] !== ShopOrderEnum::SHIP){
            $this->setError('此订单' . ShopOrderEnum::getStatus($order_relate['status']) . '，不能发货！');
            return false;
        }
        $upd_arr = [
            'express_company_id'=> $request['express_company_id'],
            'express_number'    => $request['express_number'],
            'status'            => ShopOrderEnum::SHIPPED,
            'shipment_at'       => time(),
            'updated_at'        => time()
        ];
        DB::beginTransaction();
        if (!ShopOrderRelateRepository::getUpdId($where,$upd_arr)){
            $this->setError('发货失败，请重试！');
            DB::rollBack();
            return false;
        }
        #添加库存台帐变更流水
        $shopInventorService = new ShopInventorService();
        $order_goods_list = ShopOrderGoodsRepository::getAllList(['order_relate_id' => $order_relate['id']]);
        foreach ($order_goods_list as $value){
            if (!$shopInventorService->updateInventor($order_relate['order_id'],$value['goods_id'],$value['spec_relate_id'],$value['number'],-1)){
                $this->setError($shopInventorService->error);
                DB::rollBack();
                return false;
            }
        }
        #通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $order_relate['member_id']])){
            $member_name = $member['ch_name'];
            $member_name = substr($member_name,0,3) . MemberEnum::getSex($member['sex']);
            $order_no    = MemberOrdersRepository::getField(['id' => $order_relate['order_id']],'order_no');
            $sms_template =
                MessageEnum::getTemplate(
                    MessageEnum::SHOPOORDER,
                    'shipment',
                    ['member_name' => $member_name,'order_no' => $order_no,'express_company_name' => $express_company['company_name'],'express_number' => $request['express_number']]
                );
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template);
            }
            $title = '商城订单通知';
            #发送站内信
            SendService::sendMessage($order_relate['member_id'],MessageEnum::SHOPOORDER,$title,$sms_template,$request['order_relate_id']);
        }
        $this->setMessage('发货成功！');
        DB::commit();
        return true;
    }

    /**
     * 获取评论中商品规格
     * @param array $data
     * @return array
     */
    protected function getCommentList(array $data)
    {
        $related_ids = array_column($data, 'related_id');
        $order_ids = [];
        $goods_ids = [];
        foreach ($related_ids as $related_id) {
            $related_arr = explode(',', $related_id);
            $order_ids[] = reset($related_arr);
            $goods_ids[] = end($related_arr);
        }
        $order_goods_where = ['order_relate_id' => ['in', $order_ids], 'goods_id' => ['in', $goods_ids]];
        $order_goods_list = ShopOrderGoodsRepository::getAllList($order_goods_where);
        $spec_relate_list = ShopGoodsSpecRelateRepository::getAllList(['id' => ['in', array_column($order_goods_list, 'spec_relate_id')]]);
        $spec_ids = implode(',', array_column($spec_relate_list, 'spec_ids'));
        $spec_list = ShopGoodsSpecRepository::getAssignList(explode(',', $spec_ids), ['id', 'spec_value', 'spec_name']);
        foreach ($data as &$value) {
            $related_id = explode(',', $value['related_id']);
            $spec_str = '';
            if ($spec_relate = $this->searchArray($spec_relate_list, 'id', reset($related_id))) {
                if ($spec_relate = $this->searchArray($spec_relate, 'goods_id', end($related_id))) {
                    $value_spec_ids = explode(',', trim(reset($spec_relate)['spec_ids'], ','));
                    foreach ($value_spec_ids as $value_spec_id) {
                        if ($item_spec = $this->searchArray($spec_list, 'id', $value_spec_id)) {
                            $spec_str .= reset($item_spec)['spec_name'] . ':' . reset($item_spec)['spec_value'] . ';';
                        }
                    }
                }

            }
            $value['spec_str'] = $spec_str;
        }
        return $data;
    }
    /**
     * 删除订单
     * @param $order_relate_id
     * @param $member_id
     * @return bool
     */
    public function deleteOrder($order_relate_id, $member_id)
    {
        if (!$order = ShopOrderRelateRepository::getOne(['id' => $order_relate_id,'member_id' => $member_id])){
            $this->setError('订单不存在！');
            return false;
        }
        if ($order['deleted_at'] != 0){
            $this->setError('订单已删除！');
            return false;
        }
        if (!in_array($order['status'],[ShopOrderEnum::CANCELED,ShopOrderEnum::CANCELED,ShopOrderEnum::RECEIVED])){
            $this->setError('当前状态不可以删除！');
            return false;
        }
        if (ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['deleted_at' => time(),'updated_at' => time()])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 银联支付回调
     * @param $order_id
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public static function payCallBack($order_id, $status){
        if (!$order_relate = ShopOrderRelateRepository::getOne(['order_id' => $order_id])){
            Loggy::write('error','支付回调：订单相关信息不存在！订单ID：'.$order_id.'，支付结果：'.$status);
            Throw new \Exception('订单相关信息不存在！');
        }
        if (!ShopOrderRelateRepository::getUpdId(['order_id' => $order_id],['status' => $status,'updated_at' => time()])){
            Loggy::write('error','积分赠送失败！消费积分：'.$order_relate['income_score'].' ,会员ID：'.$order_relate['member_id']);
            Loggy::write('error','支付回调：订单状态更新失败！订单ID：'.$order_id.'，支付结果：'.$status);
            Throw new \Exception('订单状态更新失败！');
        }
        if ($status == ShopOrderEnum::SHIP){
            $scoreService = new RecordService();
            if (!$scoreService->increaseScore(2,$order_relate['income_score'],$order_relate['member_id'],'购买商品','活动消费积分',false)){
                Loggy::write('error','积分赠送失败！消费积分：'.$order_relate['income_score'].' ,会员ID：'.$order_relate['member_id']);
            }
        }
        return true;
    }

    /**
     * 提醒发货
     * @param $order_relate_id
     * @return bool
     */
    public function remindToShip($order_relate_id){
        #由于没有商家，无法确认提醒通知人，待定
        $this->setMessage('提醒成功！');
        return true;
    }

    /**
     * 修改我的订单
     * @param $request
     * @return bool
     */
    public function editMyOrder($request){
        $member = Auth::guard('member_api')->user();
        if (!MemberAddressRepository::exists(['id' => $request['address_id'],'member_id' => $member->id])){
            $this->setError('收货地址不存在！');
            return false;
        }
        if (!$order = ShopOrderRelateRepository::getOne(['id' => $request['order_relate_id'],'member_id' => $member->id])){
            $this->setError('订单不存在！');
            return false;
        }
        if ($order['status'] > ShopOrderEnum::SHIP){
            $this->setError('此订单已发货，无法修改收货地址');
        }
        if (!ShopOrderRelateRepository::getUpdId(['id' => $request['order_relate_id']],['address_id' => $request['address_id'],'updated_at' => time()])){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

}
            