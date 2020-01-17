<?php


namespace App\Services\Pay;
use App\Enums\OrderEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\TradeEnum;
use App\Exceptions\PayException\OrderNotExistException;
use App\Exceptions\PayException\OrderUpdateFailedException;
use App\Exceptions\PayException\TradeUpdateFailedException;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\Activity\RegisterService;
use App\Services\BaseService;
use App\Services\Shop\OrderRelateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class UmsPayDbService extends BaseService
{
    protected $auth;
    /**
     * umsPayDbService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 支付成功后更新订单（微信、银联共用）
     * @param $requestData
     * @return mixed
     * @throws OrderNotExistException
     * @throws OrderUpdateFailedException
     * @throws TradeUpdateFailedException
     * @throws \Exception
     * @desc 有问题一定要抛出异常，才能让回调了解并返回
     */
    public function updateOrder($requestData,$paymenth = 'umspay'){
        //支付所用字段：
        //"queryId"：查询ID,
        //"orderno":订单号,
        //"cod"：支付金额,
        //"payway"：支付方式, 参见 App\Library\UmsPay\Utils\UmsPayWay中的值
        //"cardid"：卡号/支付号  是  现金支付时空格补充
        //可以为空的字段（用不到的字段）
            //"banktrace"：系统参考号 （当刷卡交易时必需要有此项）
            //"postrace"：POS 机的流水号  当刷卡交易时必需要有此项
            //"tracetime"：交易时间  在收单系统完成金融交易的具体时间
            //"signflag"本人 签收标记         0: 本 人 签收 1 ： 他 人 签收
            //"signer" 签收人  是  用于填写实际的签收人姓名
            //"dssn"
            //"dsname"

        if (!$order = MemberOrdersRepository::getOne(['order_no' => $requestData['orderno']])){
            Loggy::write($paymenth,'订单支付完成回调，订单不存在！订单号：'.$requestData['orderno'],$requestData);
            Throw new OrderNotExistException();
        }
        DB::beginTransaction();
        //更新订单状态
        if (!MemberOrdersRepository::getUpdId(['order_no' => $requestData['orderno']],['status' => OrderEnum::STATUSSUCCESS,'updated_at' => time()])){
            Loggy::write($paymenth,'订单支付完成回调，订单状态更新失败！订单号：'.$requestData['orderno'],$requestData);
            DB::rollBack();
            Throw new OrderUpdateFailedException();
        }
        //更新交易状态
        if (!MemberTradesRepository::getUpdId(['id' => $order['trade_id']],['status' => TradeEnum::STATUSSUCCESS,'end_at' => time(),'transaction_no' => $requestData['transaction_no'] ?? null])){
            Loggy::write($paymenth,'订单支付完成回调，订单状态更新失败！订单号：'.$requestData['orderno'],$requestData);
            DB::rollBack();
            Throw new TradeUpdateFailedException();
        }
        //更新订单关联的表
        switch ($order['order_type']){
            case 1://会员充值
                //TODO 待开发
                break;
            case 2://参加活动
                try{
                    RegisterService::payCallBack($order['order_no']);
                }catch (\Exception $exception){
                    DB::rollBack();
                    Throw new OrderUpdateFailedException($exception->getMessage());
                }
                break;
            case 3://精选生活，无
                break;
            case 4://商城
                try{
                    OrderRelateService::payCallBack($order['id'],ShopOrderEnum::SHIP);
                }catch (\Exception $exception){
                    DB::rollBack();
                    Throw new OrderUpdateFailedException($exception->getMessage());
                }
                break;
            default:
                return true;
        }
        DB::commit();
        return true;
    }

    /**
     * @param $requestData
     * @return mixed
     * @throws \Exception
     * @desc 有问题一定要抛出异常，才能让回调了解并返回
     */
    public function refundUpdateOrder($requestData){
        //退款所用字段
        //"orderno":订单号
        //"cod"：金额
        //"cardid"：卡号/支付号  是  现金支付时空格补充
        //可以为空的字段（用不到的字段）
            //"banktrace"： 系统参考号 （当刷卡交易时必需要有此项）
            //"postrace"：POS 机的流水号  当刷卡交易时必需要有此项
            //"cxbanktrace"：撤消的系统参考号 （与banktrace相同）
    }

}