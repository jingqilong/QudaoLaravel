<?php


namespace App\Services\Shop;


use App\Enums\CommonAuditStatusEnum;
use App\Enums\OrderEnum;
use App\Enums\ProcessCategoryEnum;
use App\Enums\ShopGoodsEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\ShopOrderTypeEnum;
use App\Enums\TradeEnum;
use App\Repositories\CommonExpressRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesRepository;
use App\Repositories\ShopCartRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopOrderGoodsRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Repositories\ShopOrderRelateViewRepository;
use App\Services\BaseService;
use App\Services\Member\AddressService;
use App\Services\Member\TradesService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class NegotiableOrderService extends BaseService
{
    use BusinessTrait,HelpTrait;
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
     * 后台获取面议订单列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getNegotiableOrderList($request)
    {
        $employee       = Auth::guard('oa_api')->user();
        $keywords       = $request['keywords'] ?? null;
        $status         = $request['status'] ?? null;
        $order_no       = $request['order_no'] ?? null;
        $express_number = $request['express_number'] ?? null;
        $receive_method = $request['receive_method'] ?? null;
        $express_company_id = $request['express_company_id'] ?? null;
        $order          = 'id';
        $desc_asc       = 'desc';
        $where          = ['id' => ['<>',0],'order_relate_type' => ShopOrderTypeEnum::NEGOTIABLE,'deleted_at' => 0];
        $column         = ['id','status','express_company_id','express_price','express_number','remarks','receive_method','order_no','amount','payment_amount','receive_name','receive_mobile','member_name','member_mobile','created_at','audit'];
        if (!is_null($status))              $where['status']  = $status;
        if (!is_null($order_no))            $where['order_no']  = $order_no;
        if (!is_null($express_number))      $where['express_number']  = $express_number;
        if (!is_null($receive_method))      $where['receive_method']  = $receive_method;
        if (!is_null($express_company_id))  $where['express_company_id']  = $express_company_id;
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
        $express_company_list = createArrayIndex($express_company_list,'id');
        foreach ($order_list['data'] as &$value){
            $value['amount'] = sprintf('%.2f',round($value['amount'] / 100,2));
            $value['payment_amount'] = sprintf('%.2f',round($value['payment_amount'] / 100,2));
            $value['express_price'] = sprintf('%.2f',round($value['express_price'] / 100,2));
            $value['status_title'] = ShopOrderEnum::getStatus($value['status']);
            $value['express_company']   = '-';
            $value['express_company_code']   = '';
            if (isset($express_company_list[$value['express_company_id']])){
                $value['express_company'] = $express_company_list[$value['express_company_id']]['company_name'];
                $value['express_company_code'] = $express_company_list[$value['express_company_id']]['code'];
            }
            $value['receive_method']    = ShopOrderEnum::getReceiveMethod($value['receive_method']);
            $value['express_number']    = $value['express_number'] ?? '-';
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::SHOP_NEGOTIABLE_ORDER,$employee->id);
        }
        $this->setMessage('获取成功！');
        return $order_list;
    }

    /**
     * 获取面议订单详情
     * @param $order_relate_id
     * @return array|bool
     */
    public function getNegotiableOrderDetails($order_relate_id){
        $employee = Auth::guard('oa_api')->user();
        if (!ShopOrderRelateRepository::exists(['id' => $order_relate_id,'order_type' => ShopOrderTypeEnum::NEGOTIABLE])){
            $this->setError('订单不存在！');
            return false;
        }
        $orderRelateService = new OrderRelateService();
        if (!$order_info = $orderRelateService->orderDetail($order_relate_id)){
            $this->setError($orderRelateService->error);
            return false;
        }
        $this->setMessage('获取订单成功！');
        return $this->getBusinessDetailsProcess($order_info,ProcessCategoryEnum::SHOP_NEGOTIABLE_ORDER,$employee->id);
    }

    /**
     * 提交面议订单
     * @param $request
     * @return bool|mixed
     */
    public function submitNegotiableOrder($request){
        if (Cache::has($request['token'])){
            $this->setError('请勿重复提交！');
            return false;
        }
        $member = Auth::guard('member_api')->user();
        $goods_json = json_decode($request['goods_json'],true);
        #检查库存
        $goodsSpecRelateService = new GoodsSpecRelateService();
        if (!$goodsSpecRelateService->checkStock($goods_json)){
            $this->setError($goodsSpecRelateService->error);
            return false;
        }
        $submit_order_info  = $this->getNegotiablePlaceOrderDetail($request);
        $express_price      = $submit_order_info['express_price'];
        DB::beginTransaction();
        #创建订单
        if (!$order_info = MemberOrdersRepository::addNegotiableGoodsOrder($member->id)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建总订单记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #创建交易记录
        $TradesService = new TradesService();
        if (!$TradesService->tradesUpdOrder($order_info['id'],$member->id,0,0,'+',0,TradeEnum::STATUSTRADING)){
            $this->setError('订单创建失败！');
            Loggy::write('order','创建交易记录失败！用户ID：'.$member->id.'，提交数据：'.json_encode($request));
            DB::rollBack();
            return false;
        }
        #添加订单关联信息
        $order_relate_arr = [
            'order_id'          => $order_info['id'],
            'member_id'         => $member->id,
            'status'            => ShopOrderEnum::PAYMENT,
            'audit'             => CommonAuditStatusEnum::SUBMIT,
            'order_type'        => ShopOrderTypeEnum::NEGOTIABLE,
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
        #开启流程
        $start_process_result = $this->addNewProcessRecord($order_relate_id,ProcessCategoryEnum::SHOP_NEGOTIABLE_ORDER);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        Cache::put($request['token'],$request['token'],5);
        $this->setMessage('下单成功！');
        return [
            'order_relate_id'   => $order_relate_id,
            'status'            => 1,#此状态1表示不需要支付，2表示需要支付
            'order_no'          => $order_info['order_no']
        ];
    }

    /**
     * 审核面议订单
     * @param $order_relate_id
     * @param $audit
     * @return bool
     */
    public function auditNegotiableOrder($order_relate_id, $audit){
        if (!$order_info = ShopOrderRelateViewRepository::getOne(['id' => $order_relate_id,'deleted_at' => 0])){
            $this->setError('订单信息不存在！');
            return false;
        }
        if ($order_info['audit'] > CommonAuditStatusEnum::SUBMIT){
            $this->setError('订单已审核！');
            return false;
        }
        $upd = ['audit' => $audit,'updated_at' => time()];
        if ($audit == CommonAuditStatusEnum::NO_PASS){
            $upd['status'] = ShopOrderEnum::CANCELED;
        }
        #更新订单状态
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],$upd)){
            $this->setError('面议订单审核失败！');
            return false;
        }
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 录入面议订单金额（审核通过后）
     * @param $request
     * @return bool
     */
    public function setNegotiableOrderAmount($request){
        $order_relate_id = $request['order_relate_id'];
        $amount          = $request['amount'];
        $express_price   = $request['express_price'] ?? null;
        if (!$order_info = ShopOrderRelateViewRepository::getOne(['id' => $order_relate_id,'deleted_at' => 0])){
            $this->setError('订单信息不存在！');
            return false;
        }
        if ($order_info['order_status'] == OrderEnum::STATUSSUCCESS){
            $this->setError('该订单已录入订单金额，不能重复操作！');
            return false;
        }
        if ($order_info['order_status'] == OrderEnum::STATUSCLOSE){
            $this->setError('该订单已取消，不能进行此操作！');
            return false;
        }
        if ($order_info['audit'] == CommonAuditStatusEnum::SUBMIT){
            $this->setError('该订单还未审核，不能进行此操作！');
            return false;
        }
        if ($order_info['audit'] != CommonAuditStatusEnum::PASS){
            $this->setError('该订单审核未通过，不能进行此操作！');
            return false;
        }
        DB::beginTransaction();
        #更新订单信息
        $upd_order_relate = ['status' => ShopOrderEnum::SHIP,'updated_at' => time()];
        if (!is_null($express_price)) $upd_order_relate['express_price'] = $express_price * 100;
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],$upd_order_relate)){
            $this->setError('操作失败！');
            DB::rollBack();
            Loggy::write('order','设置面议订单金额失败！原因：更新订单相关表失败！order_relate_id:'.$order_relate_id);
            return false;
        }
        #更新总订单信息
        $payment_amount = (is_null($express_price) ? $order_info['express_price'] : ($express_price * 100)) + ($amount * 100);
        $upd_order = [
            'amount'        => $payment_amount,
            'payment_amount'=> $payment_amount,
            'status'        => OrderEnum::STATUSSUCCESS,
            'updated_at'    => time()
        ];
        if (!MemberOrdersRepository::getUpdId(['id' => $order_info['order_id']],$upd_order)){
            $this->setError('操作失败！');
            DB::rollBack();
            Loggy::write('order','设置面议订单金额失败！原因：更新总订单信息失败！order_relate_id:'.$order_relate_id.',order_no:'.$order_info['order_no']);
            return false;
        }
        #更新交易信息
        $upd_trad = [
            'amount'        => $payment_amount,
            'trade_method'  => TradeEnum::OFFLINE,
            'status'        => TradeEnum::STATUSSUCCESS,
            'end_at'        => time()
        ];
        if (!MemberTradesRepository::getUpdId(['id' => $order_info['trade_id']],$upd_trad)){
            $this->setError('操作失败！');
            DB::rollBack();
            Loggy::write('order','设置面议订单金额失败！原因：更新交易信息失败！order_relate_id:'.$order_relate_id.',trade_id:'.$order_info['trade_id']);
            return false;
        }
        DB::commit();
        $this->setMessage('操作成功！');
        return true;
    }


    /**
     * 获取申请人ID
     * @param $order_relate_id
     * @return mixed
     */
    public function getCreatedUser($order_relate_id){
        return ShopOrderRelateRepository::getField(['id' => $order_relate_id],'member_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $order_relate_ids
     * @return array
     */
    public function getProcessBusinessList($order_relate_ids){
        if (empty($order_relate_ids)){
            return [];
        }
        $column     = ['id','member_id'];
        if (!$order_list = ShopOrderRelateRepository::getAssignList($order_relate_ids,$column)){
            return [];
        }
        $member_ids  = array_column($order_list,'member_id');
        $member_list = MemberBaseRepository::getAssignList($member_ids,['id','ch_name','mobile']);
        $member_list = createArrayIndex($member_list,'id');
        $result_list = [];
        foreach ($order_list as $value){
            $member = $member_list[$value['member_id']] ?? [];
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => '商品面议',
                'member_id'     => $value['member_id'],
                'member_name'   => $member['ch_name'] ?? '',
                'member_mobile' => $member['mobile'] ?? '',
            ];
        }
        return $result_list;
    }
}