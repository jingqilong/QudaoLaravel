<?php
namespace App\Services\Shop;


use App\Enums\MemberEnum;
use App\Enums\OrderEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\TradeEnum;
use App\Services\BaseService;
use App\Services\Common\ExpressService;
use App\Services\Member\AddressService;
use App\Repositories\{CommonExpressRepository,
    MemberAddressRepository,
    MemberOrdersRepository,
    MemberRepository,
    MemberTradesRepository,
    ScoreCategoryRepository,
    ShopGoodsRepository,
    ShopOrderGoodsRepository,
    ShopOrderRelateRepository,
    ShopOrderRelateViewRepository};
use App\Services\Common\SmsService;
use App\Services\Member\TradesService;
use App\Services\Score\RecordService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class OrderRelateService extends BaseService
{
    use HelpTrait;

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
        $goods_list         = ShopGoodsRepository::getList(['id' => ['in',$goods_ids]]);
        #购买所得积分
        $buy_score          = 0;
        #邮费
        $express_price      = 0;
        #订单总金额
        $total_price        = 0;
        $scoreService = new RecordService();
        foreach ($goods_param as $value){
            if ($goods = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $buy_score          += reset($goods)['gift_score'];
                $express_price      += reset($goods)['express_price'];
                $total_price        += reset($goods)['price'];
            }
        }
        $total_price            = sprintf('%.2f',round($total_price / 100,2));
        #可抵扣积分
        $score_deduction        = $scoreService->getUsableScore($member->m_id,$goods_param,$goods_list,$total_price);
        $express_title          = empty($express_price) ? '包邮' : '江浙沪地区包邮，其它地区总邮费：' . sprintf('%.2f',round($express_price / 100,2)).'元';
        $member                 = Auth::guard('member_api')->user();
        #收货地址
        $res['address']         = AddressService::getDefaultAddress($member->m_id);
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
     * 提交订单
     * @param $request
     * @return bool|mixed
     */
    public function submitOrder($request)
    {
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
            if (!$scoreService->expenseScore($score_type,$score_deduction,$member->m_id,'商品抵扣')){
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
        #创建交易记录
        $TradesService = new TradesService();
        $trade_amount = $payment_price;
        if ($payment_price === 0){
            $trade_amount = $trade_score;
        }
        $trade_method = 0;
        if ($trade_amount === 0 && $trade_score !== 0){
            $trade_method = TradeEnum::SCORE;
        }
        $trade_status = $order_add_arr['status'] == OrderEnum::STATUSSUCCESS ? TradeEnum::STATUSSUCCESS : TradeEnum::STATUSTRADING;
        if (!$TradesService->tradesUpdOrder($order_id,$member->m_id,0,$trade_amount,'+',$trade_method,$trade_status)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建交易记录失败！用户ID：'.$member->m_id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单关联信息
        $order_relate_arr = [
            'order_id'          => $order_id,
            'member_id'         => $member->m_id,
            'status'            => $order_add_arr == OrderEnum::STATUSSUCCESS ? ShopOrderEnum::SHIP : ShopOrderEnum::PAYMENT,
            'express_price'     => ($request['express_type'] == 1) ? $express_price * 100 : 0,
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
        #扣除库存
        if (!$goodsSpecRelateService->updStock($goods_json)){
            $this->setError($goodsSpecRelateService->error);
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
        $where = ['member_id' => $member->m_id,'id' => $order_relate_id,'deleted_at' => 0];
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
        DB::beginTransaction();
        #更新订单关联表信息
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::CANCELED,'updated_at' => time()])){
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
        if (!MemberTradesRepository::getUpdId(['id' => $order_relate['trade_id']],['status' => TradeEnum::STATUSFAIL])){
            $this->setError('取消订单失败！');
            DB::rollBack();
            return false;
        }
        #归还库存
        $order_goods_list = ShopOrderGoodsRepository::getList(['order_relate_id' => $order_relate_id]);
        $goodsSpecRelateService = new GoodsSpecRelateService();
        if (!$goodsSpecRelateService->updStock($order_goods_list,'+')){
            $this->setError($goodsSpecRelateService->error);
            DB::rollBack();
            return false;
        }
        //退还积分
        if (!empty($order_relate['score_type'])){
            $scoreService = new RecordService();
            if (!$scoreService->increaseScore($order_relate['score_type'],$order_relate['score_deduction'],$member->m_id,'商品抵扣积分退还','取消订单退还')){
                $this->setError('积分退还失败！');
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
        $member = Auth::guard('member_api')->user();
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $status     = $request['status'] ?? null;
        $where      = ['id' => ['<>',0],'member_id' => $member->m_id];
        if (!is_null($status)){
            $where['status']    = $status;
        }
        $column = ['id','status','payment_amount'];
        if (!$order_list = ShopOrderRelateViewRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $order_list = $this->removePagingField($order_list);
        if (empty($order_list['data'])){
            $this->setMessage('您还没有订单，快去购买吧！');
            return $order_list;
        }
        $order_relate_ids   = array_column($order_list['data'],'id');
        $order_goods_list   = ShopOrderGoodsRepository::getList(['order_relate_id' => ['in',$order_relate_ids]]);
        $goods_list         = GoodsSpecRelateService::getListCommonInfo($order_goods_list);
        foreach ($order_list['data'] as &$value){
            $value['payment_amount'] = sprintf('%.2f',round($value['payment_amount'] / 100,2));
            if ($search_goods_list = $this->searchArray($order_goods_list,'order_relate_id',$value['id'])){
                foreach ($search_goods_list as $item){
                    if ($goods = $this->searchArray($goods_list,'order_relate_id',$item['order_relate_id'])){
                        $value['goods_list'][] = reset($goods);
                    }
                }
            }
            $value['status'] = ShopOrderEnum::getStatus($value['status']);
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
        $where  = ['id' => $order_relate_id,'deleted_at' => 0];
        if (!empty($member_id)){
            $where['member_id'] = $member_id;
        }
        $column = ['id','status','express_company_id','express_price','express_number','remarks','receive_method','order_no','trade_id','amount','payment_amount','score_deduction','score_type','receive_name','receive_mobile','receive_area_code','receive_address','shipment_at','receive_at'];
        if (!$order = ShopOrderRelateViewRepository::getOne($where,$column)){
            $this->setError('订单不存在！');
            return false;
        }
        $order['status_title']  = ShopOrderEnum::getStatus($order['status']);
        $order['receive_method']= ShopOrderEnum::getReceiveMethod($order['receive_method']);
        $order['express_price'] = sprintf('%.2f',round($order['express_price'] / 100,2));
        $order['amount']        = sprintf('%.2f',round($order['amount'] / 100,2));
        $order['payment_amount']= sprintf('%.2f',round($order['payment_amount'] / 100,2));
        $order['shipment_at']   = empty($order['shipment_at']) ? 0 : date('Y-m-d H:i:s',$order['shipment_at']);
        $order['receive_at']    = empty($order['receive_at']) ? 0 : date('Y-m-d H:i:s',$order['receive_at']);
        $order['trade_no']          = '';
        $order['transaction_no']    = '';
        $order['trade_method']      = '';
        if (!empty($order['trade_id'])){
            if ($trade = MemberTradesRepository::getOne(['id' => $order['trade_id']])){
                $order['trade_no']          = $trade['trade_no'];
                $order['transaction_no']    = $trade['transaction_no'];
                $order['trade_method']      = TradeEnum::getTradeMethod($trade['trade_method']);
            }
        }
        $order['express_company_code'] = '';
        if (!empty($order['express_company_id'])){
            $order['express_company_code'] = CommonExpressRepository::getField(['id' => $order['express_company_id']],'code');
        }
        list($order['receive_area_address'])  = $this->makeAddress($order['receive_area_code'],$order['receive_address']);
        $order_goods_list       = ShopOrderGoodsRepository::getList(['order_relate_id' => $order['id']]);
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
     */
    public function getOrderExpressDetails($code, $number)
    {
        if (!$expressInfo = CommonExpressRepository::getOne(['code' => $code,'status' => 1])){
            $this->setError('快递公司不存在!');
            return false;
        }
        $expressDetail = ExpressService::getExpressDetails($code, $number);
        if ($expressDetail['status'] != 200){
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
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $keywords       = $request['keywords'] ?? null;
        $status         = $request['status'] ?? null;
        $order_no       = $request['order_no'] ?? null;
        $express_number = $request['express_number'] ?? null;
        $receive_method = $request['receive_method'] ?? null;
        $express_company_id = $request['express_company_id'] ?? null;
        $order          = 'id';
        $desc_asc       = 'desc';
        $where          = ['id' => ['<>',0]];
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
            if (!$order_list = ShopOrderRelateViewRepository::search($keywords_column,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$order_list = ShopOrderRelateViewRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
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
        if (!$order_relate = ShopOrderRelateRepository::getOne($where)){
            $this->setError('订单不存在！');
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
        if (!ShopOrderRelateRepository::getUpdId($where,$upd_arr)){
            $this->setError('发货失败，请重试！');
            return false;
        }
        #通知用户
        if ($member = MemberRepository::getOne(['m_id' => $order_relate['member_id']])){
            $member_name = $member['m_cname'];
            $member_name = substr($member_name,0,1) . MemberEnum::getSex($member['m_sex']);
            $order_no    = MemberOrdersRepository::getField(['id' => $order_relate['order_id']],'order_no');
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $sms_template = '尊敬的'.$member_name.'您好！您的订单：'.$order_no .'已发货,快递公司：'.$express_company['company_name'] . '，快递单号：' . $request['express_number'] . '。';
                $smsService->sendContent($member['m_phone'],$sms_template);
            }
        }
        $this->setMessage('发货成功！');
        return true;
    }

    /**
     * Oa根据订单号获取物流状态
     * @param $code
     * @param $number
     * @return bool
     */
    public function getOaOrderExpressDetails($code, $number)
    {
        if (!$expressInfo = CommonExpressRepository::getOne(['code' => $code,'status' => 1])){
            $this->setError('快递公司不存在!');
            return false;
        }
        $expressDetail = ExpressService::getExpressDetails($code, $number);
        if ($expressDetail['status'] != 200){
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
     * OA获取快递列表
     * @return bool|null
     */
    public function getExpressList()
    {
        if (!$list = CommonExpressRepository::getList(['id' => ['>',1],'status' => 1],['*'],'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        $this->setMessage('获取成功!');
        return $list;
    }
}
            