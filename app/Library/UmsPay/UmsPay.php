<?php
namespace App\Library\UmsPay;

use App\Library\UmsPay\Utils\UmsConstants;
use Tolawho\Loggy\Facades\Loggy;
use Ixudra\Curl\Facades\Curl;
use App\Library\UmsPay\Utils\UmsQrType;
use App\Library\UmsPay\Utils\UmsPayWay;

/**
 * Class UmsPay
 * @package App\Library\UmsPay
 *
 * 测试代码示例：
 * $umsPay = new UmsPay();
 * $order_no = date("Ymdhis");
 * $response = $umsPay->createOrder($order_no,0.01);//不分账支付请求，获取app支付要素
 * $query_id = $response['content']['queryId'];
 * $response = $umsPay->closeOrder($order_no, $query_id);
 * 以下是银联提供的测式参数
 * $response = $umsPay->queryClearDate("201901041549161","20190104");//根据清算日期和单号查询订单支付情况
 * $response = $umsPay->queryTransDate("201901041549161","20190104");//根据交易日期和单号查询订单支付情况
 * $response = $umsPay->queryBySystemCode("21190122100423194476");//根据查询流水号查询订单支付情况
 * $response = $umsPay->refund("21190122100423194476");//不分账退款
 *
 */
class UmsPay
{

    public $pay_way = UmsPayWay::PAY_WAY_WECHAT;

    public $sign_type = 'MD5';

    public $notify_url = '';


    /**
     * UmsPay constructor.
     */
    public function __construct()
    {
        $this->notify_url = $this->getNotifyUrl();
    }


    /**
     * @desc 支付接口
     * @param string $order_no 支付单号
     * @param string $cod 金额
     * @param string $busi_order_no 商户业务订单号 选填：业务订单号，支付通知会回传
     * @param string $memo 备注  100  非必填
     * @param string $order_desc  订单信息  100  非必填，为空时支付完成后展示的商户
     * @return string 返回给前端页面
     */
    public function createOrder($order_no,$cod, $busi_order_no='',$memo='',$order_desc='') {
        //接口返回说明
        //code Y  00 表示成功，其他标识表示失败
        //msg  Y  失败原因
        //orderId N  商户下单时的单号 order_no
        //status  N  订单状态（支付状态）参考Class UmsStatus  取值说明，当 code=00 时， 后面的参数才会有值， 支付状态以异步通知为准
        //memo  N  备注信息
        //payway  N  支付方式
        //mer_id  N  商户 id
        //refId  N  银联系统交易参考号
        //queryId  N  查询流水号
        //cod  N  订单金额
        //mac  N  签名，code=02 时 mac 为空
        //returnURL  文档中没有，微信群中说，这是返回的URL。（）
        $order_map = [];
        $order_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $order_map['order_no'] = $order_no;
        if('test' == UmsConstants::PAY_ENV){  //测试时只支付0.01元
            $order_map['cod'] = 0.01;
        }else{
            $order_map['cod'] = $cod;
        }
        $order_map['qrtype'] =  UmsQrType::QR_TYPE_H5;
        $order_map['payway'] =  $this->pay_way;

        if(!empty($busi_order_no)){
            $order_map['busi_order_no']  = $busi_order_no;
        }
        if(!empty($memo)){
            $order_map['memo']  = $memo;
        }
        if(!empty($order_desc)){
            $order_map['orderDesc']  = $order_desc;
        }

        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;

        $order_map = $this->signData($order_map);
        if('test' == UmsConstants::PAY_ENV)
            $pay_url = UmsConstants::TEST_PAY_URL;
        else
            $pay_url = UmsConstants::PAY_URL;
        //返回前端页面，用于跳转
        Loggy::write('umspay',http_build_query($order_map));
        $url = $pay_url .'?'. http_build_query($order_map);
        Loggy::write('umspay',$url);
        return  $url;
    }

    /**
     * @desc 订单提交后放弃支付，关闭订单
     * @param $order_no
     * @param $query_id
     * @return array
     */
    public function closeOrder($order_no, $query_id) {

        $order_map = [];
        $order_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $order_map['order_no'] = $order_no;
        $refund_map['qrtype'] = UmsQrType::QR_TYPE_CLOSE;
        $refund_map['queryId'] = $query_id;

        if(!empty($busi_order_no)){
            $order_map['busi_order_no']  = $busi_order_no;
        }
        if(!empty($memo)){
            $order_map['memo']  = $memo;
        }
        if(!empty($order_desc)){
            $order_map['orderDesc']  = $order_desc;
        }
        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;

        $order_map = $this->signData($order_map);
        if('test' == UmsConstants::PAY_ENV)
            $pay_url =UmsConstants::TEST_PAY_URL;
        else
            $pay_url =UmsConstants::PAY_URL;
        $response = Curl::to($pay_url)
            ->withData( $order_map )
            ->returnResponseObject()
            ->post();

        if(200 != $response->status){
            Loggy::write('umspay',$response->error);
        }
        $content = $response->content;
        if($content){
            $content = json_decode($content,true) ;
        }
        if((!isset($content['errCode'])) || ('00' != $content['errCode'])){
            Loggy::write('umspay','关闭订单发生错误',$content);
        }
        return $content;
    }

    /**
     * @desc 退款
     * @param $query_id  查询流水号
     * @param $refund_no 通款单号
     * @param $refund_amount  退款金额
     * @param string $refund_desc   退款描述  选填 50  退款通知，会原样返回
     * @return array
     */
    public function refund($query_id, $refund_no='', $refund_amount='', $refund_desc='') {
        $refund_map = [];
        $refund_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $refund_map['qrtype'] = UmsQrType::QR_TYPE_H5;
        $refund_map['queryId'] = $query_id;
        if(!empty($refund_no)){
            $refund_map['refund_no'] = $refund_no;  //date("Ymdhis")
        }
        if(!empty($refund_amount)){
            $refund_map['refund_amt'] = $refund_amount;
        }
        if(!empty($refund_desc)){
            $order_map['refund_desc']  = $refund_desc;
        }
        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;
        $refund_map = $this->signData($refund_map);
        if('test' == UmsConstants::PAY_ENV)
            $refund_url =UmsConstants::TEST_REFUND_URL;
        else
            $refund_url =UmsConstants::REFUND_URL;
        $response = Curl::to($refund_url)
            ->withData( $refund_map )
            ->returnResponseObject()
            ->post();

        if(200 != $response->status){
            Loggy::write('umspay',$response->error);
        }
        $content = $response->content;
        if($content){
            $content = json_decode($content,true) ;
        }
        if((!isset($content['errCode'])) || ('00' != $content['errCode'])){
            Loggy::write('umspay','退款发生错误',$content);
        }
        return $content;
    }

    /**
     * @desc 生成加密验签的函数
     * @access private
     * @param $params
     * @return mixed
     */
    private function signData($params){
        $str_buffer = '';
        //签名方式
        $params['signType'] = $this->sign_type;
        //异步通知回调URL
        if(!empty($this->notify_url))
            $params['notifyUrl'] = $this->notify_url;

        //进行键名排序
        ksort($params);
        foreach($params as $key => $value){
            $str_buffer .= $value;
            Loggy::write('umspay',"加密的串是：".$str_buffer);
        }
        $mac_str = strtoupper(md5($str_buffer . UmsConstants::CHECK_STR));
        $params['mac'] = $mac_str;
        Loggy::write('umspay',"MAC的值是：".$mac_str);
        return $params;
    }


    /**
     * @desc 根据查询流水号查询订单支付情况
     * @param $query_id
     * @return array
     */
    public function queryBySystemCode($query_id) {
        $query_map = [];
        $query_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $query_map['qrtype'] =  UmsQrType::QR_TYPE_H5;
        $query_map['queryId'] = $query_id;
        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;
        $query_map = $this->signData($query_map);
        if('test' == UmsConstants::PAY_ENV)
            $query_url =UmsConstants::TEST_QUERY_URL;
        else
            $query_url =UmsConstants::QUERY_URL;
        $response = Curl::to($query_url)
            ->withData( $query_map )
            ->returnResponseObject()
            ->post();

        if(200 != $response->status){
            Loggy::write('umspay',$response->error);
        }
        $content = $response->content;
        if($content){
            $content = json_decode($content,true) ;
        }
        if((!isset($content['errCode'])) || ('00' != $content['errCode'])){
            Loggy::write('umspay','查询发生错误',$content);
        }
        return $content;
    }

    /**
     * @desc 根据交易日期和单号查询订单支付情况
     * @param $way_bill_no
     * @param $trans_date
     * @return array
     */
    public function queryTransDate($way_bill_no, $trans_date) {
        $query_map = [];
        $query_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $query_map['qrtype'] =  UmsQrType::QR_TYPE_H5;
        $query_map['waybillno'] = $way_bill_no;
        $query_map['transDate'] = $trans_date;
        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;
        $query_map = $this->signData($query_map);
        if('test' == UmsConstants::PAY_ENV)
            $query_url =UmsConstants::TEST_QUERY_URL;
        else
            $query_url =UmsConstants::QUERY_URL;
        $response = Curl::to($query_url)
            ->withData( $query_map )
            ->returnResponseObject()
            ->post();

        if(200 != $response->status){
            Loggy::write('umspay',$response->error);
        }
        $content = $response->content;
        if($content){
            $content = json_decode($content,true) ;
        }
        if((!isset($content['errCode'])) || ('00' != $content['errCode'])){
            Loggy::write('umspay','查询发生错误',$content);
        }
        return $content;
    }

    /**
     * @desc 根据清算日期和单号查询订单支付情况
     * @param $order_no
     * @param $clear_date
     * @return array
     */
    public function queryClearDate($order_no, $clear_date) {
        $query_map = [];
        $query_map['mer_id'] = UmsConstants::STATIC_MER_ID;
        $query_map['qrtype'] =  UmsQrType::QR_TYPE_CLOSE;
        $query_map['waybillno'] = $order_no;
        $query_map['date'] = $clear_date;
        $order_map['returnUrl'] = $this->getReturnUrl();
        $order_map['notifyUrl'] = $this->notify_url;
        $query_map = $this->signData($query_map);
        if('test' == UmsConstants::PAY_ENV)
            $query_url =UmsConstants::TEST_QUERY_URL;
        else
            $query_url =UmsConstants::QUERY_URL;
        $response = Curl::to($query_url)
            ->withData( $query_map )
            ->returnResponseObject()
            ->post();

        if(200 != $response->status){
            Loggy::write('umspay',$response->error);
        }
        $content = $response->content;
        if($content){
            $content = json_decode($content,true) ;
        }
        if((!isset($content['errCode'])) || ('00' != $content['errCode'])){
            Loggy::write('umspay','查询发生错误',$content);
        }
        return $content;
    }

    /**
     * @desc 拼装需要返回的URL。支付后返回系统的页面中
     * @return string
     */
    private function getReturnUrl(){
        //可以传回的参数
        //"code","msg","orderId","status","memo","mer_id",
        //"refId","queryId","payway","tracetime","cod","mac"
        if('test' == UmsConstants::PAY_ENV){
            $url = UmsConstants::TEST_RETURN_URL;
        }else{
            $url = UmsConstants::RETURN_URL;
        }
        $return_url = $url . UmsConstants::RETURN_URL_HASH; ;
        //return urlencode($return_url);
        return $return_url;
    }

    /**
     * @desc 拼装需要回调的URL。支付后返回系统的页面中
     * @return string
     */
    private function getNotifyUrl(){
        if('test' == UmsConstants::PAY_ENV){
            $url = UmsConstants::TEST_NOTIFY_URL;
        }else{
            $url = UmsConstants::NOTIFY_URL;
        }
        //return urlencode($url);
        return $url;
    }


}