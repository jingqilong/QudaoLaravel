<?php


namespace App\Services\Pay;
use App\Enums\OrderEnum;
use App\Enums\PayMethodEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\TradeEnum;
use App\Exceptions\PayException\OrderNotExistException;
use App\Exceptions\PayException\OrderUpdateFailedException;
use App\Exceptions\PayException\TradeUpdateFailedException;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesLogRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\Shop\OrderRelateService;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
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
     * @param $requestData
     * @return mixed
     * @throws OrderNotExistException
     * @throws OrderUpdateFailedException
     * @throws TradeUpdateFailedException
     * @throws \Exception
     * @desc 有问题一定要抛出异常，才能让回调了解并返回
     */
    public function updateOrder($requestData){
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
            Loggy::write('umspay','订单支付完成回调，订单不存在！订单号：'.$requestData['orderno'],$requestData);
            Throw new OrderNotExistException();
        }
        DB::beginTransaction();
        //更新订单状态
        if (!MemberOrdersRepository::getUpdId(['order_no' => $requestData['orderno']],['status' => OrderEnum::STATUSSUCCESS])){
            Loggy::write('umspay','订单支付完成回调，订单状态更新失败！订单号：'.$requestData['orderno'],$requestData);
            DB::rollBack();
            Throw new OrderUpdateFailedException();
        }
        //更新交易状态
        if (!MemberTradesRepository::getUpdId(['id' => $order['trade_id']],['status' => TradeEnum::STATUSSUCCESS])){
            Loggy::write('umspay','订单支付完成回调，订单状态更新失败！订单号：'.$requestData['orderno'],$requestData);
            DB::rollBack();
            Throw new TradeUpdateFailedException();
        }
        //更新订单关联的表
        switch ($order['order_type']){
            case 1://会员充值
                break;
            case 2://参加活动
                break;
            case 3://精选生活
                break;
            case 4://商城
                OrderRelateService::payCallBack($order['id'],ShopOrderEnum::SHIP);
                break;
            default:
                return true;
        }
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