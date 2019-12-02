<?php
namespace App\Library\UmsPay\Utils;

use \Exception;
/**
 * Class JSONUtils
 * @package App\Library\UmsPay\Utils
 */
class JSONUtils{

    public static $CHECK_STR="11111111111111111111111111111111";//双方之间的秘钥
    //下面各个节点的key都是可以更换的，具体在测试环境的时候需要跟对接人员沟通好
    public  static $sRootName="transaction";
    public  static $sHeadName="header";
    public  static $sBodyName="body";
    public  static $requestHeadlist=["version","transtype","employno","termid","shopid","request_time"];
    //这里面的支付通知过来的key是可变的，如果商户有其他需求，需要通知更多的参数，可以在这个数组中添加通知新的参数
    public static  $requestP033bodyList=["queryId","orderno","cod","payway","banktrace","postrace","tracetime","cardid","signflag","signer","dssn","dsname"];
    public static  $requestP036bodyList=["orderno","cod","cardid","banktrace","postrace","cxbanktrace"];
    public static  $responseHeadNodes=["version","transtype","termid","employno","response_time","response_code","response_msg"];
    public static  $responseP033BodyNodes=[];
    public static  $responseP036BodyNodes=[];
    public static  $responseErrorBodyNodes=[];
    private static  $sdfLongTime;

    /**
     * @desc init the class
     */
    public static function getsdfLongTime(){
        self::$sdfLongTime = date("Ymdhis");
        return self::$sdfLongTime;
    }

    /**
     * @param $context
     * @return mixed
     * @desc 读取推送的参数值，放到json中，后面的所有操作都从该json中取值
     */
    public static function getRequestParamStream($context) {

        $ret = [];
        $ret['code'] = '00';

        try {
            $request = json_decode($context, true) ;
            //提取header节点参数值
            $header = $request["header"];
            foreach(self::$requestHeadlist as $key ){
                if(isset($header[$key])){
                    $ret[$key] = $header[$key];
                }else{
                    $ret['code'] = '01';
                    $ret['msg'] = "通知报文header中缺少参数[" .$key."]";//此处不能return，因为请求头里面还需要一些参数值再响应的时候需要用
                }
            }
            //提取header节点参数值
            $body = $request["body"];
            //根据不同的交易类型，获取参数
            if("P033" == $ret["transtype"]){//支付通知
                foreach(self::$requestP033bodyList as $key){
                    if(isset($body[$key])){
                        $ret[$key] = $body[$key];
                    }else{
                        $ret['code'] = '01';
                        $ret['msg'] = "通知报文body中缺少参数[" .$key."]";
                        return $ret ;
                    }
                }
            }elseif("P036" == $ret["transtype"]){//退款通知
                foreach(self::$requestP036bodyList as $key){
                    if(isset($body[$key])){
                        $ret[$key] = $body[$key];
                    }else{
                        $ret['code'] = '01';
                        $ret['msg'] = "通知报文body中缺少参数[" .$key."]";
                        return $ret ;
                    }
                }
            }else{
                $ret['code'] = '01';
                $ret['msg'] = "通知报文transtype参数值错误";
                return $ret ;
            }
        } catch (Exception $e) {
            $ret['code'] = '01';
            $ret['msg'] = "解析报文异常，请检查context参数是否是json格式";
        }
        return $ret ;
    }

    /**
     * @param $requestData
     * @param $responseNodes
     * @return mixed
     */
    public static function getResponseParam($requestData,$responseNodes) {
        //获取当前时间，作为response_time的值
        $response_time = self::getsdfLongTime();
        $requestData['response_time'] = $response_time;
        $ret = [];
        //组织响应的header节点内容
        $header = [];
        foreach(self::$responseHeadNodes as $key){
            $header[$key] = $requestData[$key]==null ? "" : $requestData[$key];
        }

        //组织响应的body节点内容,通知类的body都是空的，有些商户可能会有查询交易，那么他们的body里面就是有参数的
        $body = [];

        foreach($responseNodes as $key){
            $body[$key] = $requestData[$key]==null ? "" : $requestData[$key];
        }
        $ret['header'] = $header;
        $ret['body'] = $body;
        return $ret ;
    }
}