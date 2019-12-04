<?php


namespace App\Services\Pay;

use App\Enums\OrderEnum;
use App\Enums\PayMethodEnum;
use App\Enums\TradeEnum;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesLogRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tolawho\Loggy\Facades\Loggy;

class WeChatPayService extends BaseService
{
    use HelpTrait;
    protected $we_chat_pay_config;
    protected $auth;

    /**
     * WeChatPayService constructor.
     */
    public function __construct()
    {
        $this->we_chat_pay_config = config('wechat.payment.default');
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 微信支付，微信官方下单
     * @param $request
     * @return array
     */
    public function placeOrder($request)
    {
        $user = $this->auth->user();
        if (!$open_id = MemberBindRepository::getField(['user_id' => $user->id],'identifier')){
            return ['code' => 0, 'message' => '请使用微信登录后操作！'];
        }
        if (!$order = MemberOrdersRepository::getOne(['order_no' => $request['order_no']])){
            return ['code' => 0, 'message' => '订单信息不存在！'];
        }
        switch ($order['status']){
            case OrderEnum::STATUSSUCCESS:
                return ['code' => 0, 'message' => '订单已完成交易！'];
                break;
            case OrderEnum::STATUSCLOSE:
                return ['code' => 0, 'message' => '订单关闭，无法进行交易！'];
                break;
        }
        DB::beginTransaction();
        if ($order['trade_id'] == 0){#生成交易信息
            $trade_add = [
                'order_id'      => $order['id'],
                'pay_user_id'   => $user->id,
                'payee_user_id' => 0,
                'amount'        => $order['payment_amount'],
                'pay_method'    => PayMethodEnum::WECHATPAY
            ];
            if (!$trade_id = MemberTradesRepository::addTrade($trade_add)){
                DB::rollBack();
                return ['code' => 0, 'message' => '生成交易信息失败！'];
            }
            #添加交易日志
            MemberTradesLogRepository::addLog($trade_id,$trade_add['amount'],'添加交易记录',
                '用户：【'.$user->m_phone.'】于'.date('Y-m-d H:m:s').'添加了交易记录，交易金额：'.$trade_add['amount'].', 交易状态：待付款！');
            MemberOrdersRepository::getUpdId(['id' => $order['id']], ['trade_id' => $trade_id]);
            $order['trade_id'] = $trade_id;
        }
        $trade_no = MemberTradesRepository::getField(['id' => $order['trade_id']],'trade_no');
        $res = $this->weChatPay([
            'order_no'      => $order['order_no'],
            'amount'        => $order['payment_amount'],
            'open_id'       => $open_id,
            'pay_trade_no'  => $trade_no]);
        if ($res['code'] == 1){
            DB::commit();
            return ['code' => 1, 'message' => '下单成功！', 'data' => $res['data']];
        }
        DB::rollBack();
        return ['code' => 0, 'message' => '下单失败！'];
    }

    /**
     * 调用微信下单接口生成支付id
     * @param array $data  ['order_no','amount','open_id','pay_trade_no']
     * @return array
     */
    public function weChatPay(array $data){
        $config     = $this->we_chat_pay_config;
        $config['notify_url'] = url('/api/v1/pay/we_chat_pay_call_back');
        $config['app_id'] = env('WECHAT_OFFICIAL_ACCOUNT_APPID');
        $app        = Factory::payment($config);
        try {
            $res = $app->order->unify([
                'body'              => '['.$data['order_no'].']号支付',
                'out_trade_no'      => $data['pay_trade_no'],           //第三方支付接口的商户订单号',
                'total_fee'         => $data['amount'],                 //单位分
                'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],         // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'trade_type'        => 'JSAPI',                         // 请对应换成你的支付方式对应的值类型
                'openid'            => $data['open_id'],
            ]);
            if ($res['return_code'] == 'SUCCESS'){
                /*
                 * 返回值示例：
                 * [
                 * 'return_code' => 'SUCCESS',                            //此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                 * 'return_msg'  => 'OK',                                 //返回信息，如非空，为错误原因:签名失败、参数格式校验错误
                 * 'appid'       => 'wx8888888888888888',                 //调用接口提交的小程序ID
                 * 'mch_id'      => '10000100',                           //调用接口提交的商户号
                 * 'nonce_str'   => 'IITRi8Iabbblz1J',                    //微信返回的随机字符串
                 * 'openid'      => 'oUpF8uMuAJO_M2pxb1Q9zNjWeSs6o',      //openid
                 * 'sign'        => '7921E432F65EB8ED0CE9755F0E86D72F2',  //微信返回的签名值
                 * 'result_code  => 'SUCCESS',                            //SUCCESS/FAIL 下单结果
                 * 'prepay_id'   => 'wx2014111026397cbf6ffd8b0779950874', //微信生成的预支付会话标识，用于后续接口调用中使用，该值有效期为2小时
                 * 'trade_type'  => 'JSAPI'                               //交易类型，取值为：JSAPI，NATIVE，APP等
                 * ];
                 */

                if ($res['result_code'] == 'SUCCESS'){
                    Loggy::write('payment','订单号【'.$data['order_no'].'】微信支付下单成功');
                    $prepay_id   = $res['prepay_id'];
                    $return_data = [
                        'appId'     => $res['appid'],
                        'timeStamp' => time(),
                        'nonceStr'  => $this->getSignCode(),
                        'package'   => 'prepay_id='.$prepay_id,
                        'signType'  => 'MD5'
                    ];
                    $str = '';
                    foreach ($return_data as $k => $v){
                        if ($k == 'signType'){
                            $str .= $k.'='.$v;
                        }else{
                            $str .= $k.'='.$v.'&';
                        }
                    }
                    $return_data['sign'] = md5($str);
                    return ['code' => 1, 'message' => '下单成功！', 'data' => $return_data];
                }
                Loggy::write('payment','订单号【'.$data['order_no'].'】微信支付下单失败，原因：'.$res['return_msg']);
                return ['code' => 0, 'message' => $res['return_msg'], 'data' => []];
            }else{
                Loggy::write('payment','订单号【'.$data['order_no'].'】微信支付下单失败，原因：'.$res['return_msg']);
                return ['code' => 0, 'message' => $res['return_msg'], 'data' => []];
            }
        } catch (InvalidConfigException $e) {
            Loggy::write('payment',$e);
            return ['code' => 0, 'message' => $e->getMessage(), 'data' => []];
        } catch (InvalidArgumentException $e) {
            Loggy::write('payment',$e);
            return ['code' => 0, 'message' => $e->getMessage(), 'data' => []];
        } catch (GuzzleException $e) {
            Loggy::write('payment',$e);
            return ['code' => 0, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 微信支付回调
     * @return bool
     */
    public function payCallBack(){
        /*
         * 回调参数示例:
         * [
         * 'return_code' => 'SUCCESS',                            //此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
         * 'return_msg'  => 'OK',                                 //返回信息，如非空，为错误原因:签名失败、参数格式校验错误
         * 'appid'       => 'wx8888888888888888',                 //调用接口提交的小程序ID
         * 'mch_id'      => '10000100',                           //调用接口提交的商户号
         * 'nonce_str'   => 'IITRi8Iabbblz1J',                    //微信返回的随机字符串
         * 'openid'      => 'oUpF8uMuAJO_M2pxb1Q9zNjWeSs6o',      //openid
         * 'sign'        => '7921E432F65EB8ED0CE9755F0E86D72F2',  //微信返回的签名值
         * 'result_code  => 'SUCCESS',                            //SUCCESS/FAIL 下单结果
         * 'transaction_id'   => 'wx2014111026397cbf6ffd8b0779950874', //微信支付订单号
         * 'trade_type'  => 'JSAPI'                               //交易类型，取值为：JSAPI，NATIVE，APP等
         * 'time_end'    => '20141030133525'                      //支付完成时间，格式为yyyyMMddHHmmss，如2014年10月30日13点35分25秒
         * ];
         */
        $app = Factory::payment($this->we_chat_pay_config);
        try {
            $response = $app->handlePaidNotify(function ($message, $fail) {
                // 使用通知里的 "微信支付订单号" 或者 "商户交易订单号" 去自己的数据库找到订单，此处使用商户交易订单号查询
                $trade_info = MemberTradesRepository::getOne(['trade_no' => $message['out_trade_no']]);

                if (!$trade_info || $trade_info['status'] == TradeEnum::STATUSSUCCESS) { // 如果订单不存在 或者 订单已经支付过了
                    return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                }
                ///////////// <- 在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
                $check_app = Factory::payment($this->we_chat_pay_config);
                $res = $check_app->order->queryByOutTradeNumber($message['out_trade_no']);
                /*
                     * 【订单查询】返回值示例：
                     * [
                     * 'return_code' => 'SUCCESS',                            //此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                     * 'return_msg'  => 'OK',                                 //返回信息，如非空，为错误原因:签名失败、参数格式校验错误
                     * 以下字段在return_code为SUCCESS的时候有返回
                     * 'appid'       => 'wx8888888888888888',                 //调用接口提交的小程序ID
                     * 'mch_id'      => '10000100',                           //调用接口提交的商户号
                     * 'nonce_str'   => 'IITRi8Iabbblz1J',                    //微信返回的随机字符串
                     * 'sign'        => '7921E432F65EB8ED0CE9755F0E86D72F2',  //微信返回的签名值
                     * 'result_code  => 'SUCCESS',                            //SUCCESS/FAIL 下单结果
                     * 以下字段在return_code 、result_code、trade_state都为SUCCESS时有返回 ，如trade_state不为 SUCCESS，则只返回out_trade_no（必传）和attach（选传）。
                     * 'trade_state' => 'SUCCESS'                             //交易状态，SUCCESS—支付成功、REFUND—转入退款、NOTPAY—未支付、CLOSED—已关闭、USERPAYING--用户支付中
                     *                                                        //PAYERROR--支付失败(其他原因，如银行返回失败)
                     * ];
                     */
                if ($res->return_code == 'SUCCESS' && $res->result_code == 'SUCCESS' && $res->trade_state != 'SUCCESS') {
                    Loggy::write('payment','交易号【'.$trade_info['trade_no'].'】回调时查询到用户未支付成功！');
                    return true;//告诉微信，用户付款失败，别再通知我了
                }
                if ($message['return_code'] !== 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                    return $fail('通信失败，请稍后再通知我');
                }
                // 用户是否支付成功
                if (array_column($message, 'result_code') === 'SUCCESS') {
                    if (!MemberOrdersRepository::exists(['id' => $trade_info['order_id']])){
                        Loggy::write('payment','交易号【'.$trade_info['trade_no'].'】回调未找到订单信息！');
                        return $fail('订单信息未找到，请稍后再通知我');
                    }
                    $order_upd = ['status' => OrderEnum::STATUSSUCCESS, 'updated_at' => time()];
                    $trade_upd = [
                        'transaction_no' => $message['transaction_id'],
                        'status'   => TradeEnum::STATUSSUCCESS,
                        'end_at' => time(), // 更新支付时间为当前时间
                    ];

                }else{//用户支付失败
                    $order_upd = ['status' => OrderEnum::STATUSFAIL, 'updated_at' => time()];
                    $trade_upd = [
                        'transaction_no' => $message['transaction_id'],
                        'status'   => TradeEnum::STATUSFAIL,
                        'end_at' => time(), // 更新支付时间为当前时间
                    ];
                }
                //更新订单信息
                if (!MemberOrdersRepository::getUpdId(['id' => $trade_info['order_id']],$order_upd)){
                    Loggy::write('payment','【微信支付回调】交易号【'.$trade_info['trade_no'].'】更新订单信息失败！');
                }
                //更新交易信息
                if (!MemberTradesRepository::getUpdId(['trade_no' => $message['out_trade_no']],$trade_upd)){
                    Loggy::write('payment','【微信支付回调】交易号【'.$trade_info['trade_no'].'】更新交易信息失败！交易状态：'.$trade_upd['status'].'，第三方交易号：'.$message['transaction_id']);
                }
                //添加交易日志
                $status = [1 => '交易成功', 2 => '交易失败'];
                MemberTradesLogRepository::addLog($trade_info['trade_id'],$trade_info['amount'],'添加交易记录',
                    '交易号【'.$trade_info['trade_no'].'】，交易结果：'.$status[$order_upd['status']].',付款方：【'.$trade_info['pay_user_id'].'】，收款方：【'.$trade_info['payee_user_id'].'】,时间'.date('Y-m-d H:m:s').'交易金额：'.$trade_info['amount']);
                return true; // 返回处理完成
            });
            $response->send(); // return $response;
            return true;
        } catch (\EasyWeChat\Kernel\Exceptions\Exception $e) {
            Loggy::write('payment',json_encode($e));
            return false;
        }
    }

    /**
     *
     * @param $url
     * @return array|bool
     */
    public function getJsapiTicket($url)
    {
        try{
            $config         = config('wechat.official_account.default');
            $app            = Factory::officialAccount($config);

//            $access_token   = $app->oauth->getAccessToken($code);
            $ticket         = $app->jssdk->getTicket();
            $arr            = [
                'jsapi_ticket'  => $ticket['ticket'] ?? '',
                'noncestr'      => $this->getSignCode(),
                'timestamp'     => time(),
                'url'           => $url
            ];
            $str = '';
            foreach ($arr as $k=>$v){
                $str .= $k.'='.$v.'&';
            }
            $signature = sha1(trim($str,'&'));
            $res = [
                'appId'     => $config['app_id'],
                'timestamp' => $arr['timestamp'],
                'nonceStr'  => $arr['noncestr'],
                'signature' => $signature
            ];
            $this->setMessage('获取成功！');
            return $res;
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            return false;
        } catch (GuzzleException $e) {
            $this->setError($e->getMessage());
            return false;
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
}