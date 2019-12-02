<?php

namespace App\Library\UmsPay\Notify;


use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Request;
use Tolawho\Loggy\Facades\Loggy;
use App\Library\UmsPay\Utils\JSONUtils;
use App\Services\Pay\UmsPayDbService;

/**
 * Class JsonNotify
 * @package App\Library\UmsPay\Notify
 */
class JsonNotify
{

    /**
     * @param $request
     * @return mixed $response
     */
    public function doPost($request)  {

        $context = $request["context"];
        $mac = $request["mac"];
        Loggy::write('umspay',"大华异步通知的报文context是：".$context);
        Loggy::write('umspay',"大华异步通知的签名mac是：".$mac);
        $response = new Response();
        if(empty($context)){
            $response->setContent("An empty message was received.");
            Loggy::write('umspay',"收到了空的报文");
            return ;//有可能存在通知报文没有参数的情况，例如验证商户系统是否正常
        }

        //验证签名，对收到的原始报文和秘钥进行md5加密
        $localMac = MD5($context . JSONUtils::$CHECK_STR);
        //读取通知的报文内容
        $requestData = JSONUtils::getRequestParamStream($context);

        if($mac != $localMac){
            $requestData['response_code'] = '01';
            $requestData['response_msg'] ="MAC签名不一致";
            $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseErrorBodyNodes);
        }

        if("00" != $requestData.["code"]){
            $requestData['response_code'] = '01';
            $requestData['response_msg'] =$requestData["msg"];
            $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseErrorBodyNodes);
        }

        $requestData["response_code"] = "00";
        $requestData["response_msg"] = "交易成功";
        $transtype=$requestData["transtype"];

        if("P033" == $transtype){//支付通知
            try {
                //支付通知，商户自行处理,所需要的参数在map中取，参数key在repeustP033BodyNodes里面
                //此处更新数据库的动作省略....
                //银联变态垃圾代码，只能这样去调了。
                $umsPayDbService = new UmsPayDbService();
                $result= $umsPayDbService->createOrder($requestData);
                //组装响应的报文
                $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseP033BodyNodes);
            } catch (\Exception $e) {
                //如果操作数据库异常
                $requestData['response_code'] = '01';
                $requestData['response_msg'] = "系统内部异常:" . $e->getMessage();
                $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseErrorBodyNodes);
            }
        }elseif("P036" == $transtype){//退款通知
            try {
                //退款通知，商户自行处理,所需要的参数在map中取，参数key在repeustP036BodyNodes里面
                //此处更新数据库的动作省略....
                //银联变态垃圾代码，只能这样去调了。
                $umsPayDbService = new UmsPayDbService();
                $result= $umsPayDbService->refund($requestData);
                //组装响应的报文
                $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseP033BodyNodes);
            } catch (\Exception $e) {
                //如果操作数据库异常
                $requestData['response_code'] = '01';
                $requestData['response_msg'] = "系统内部异常:" . $e->getMessage();
                $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseErrorBodyNodes);
            }
        }else{
            $requestData['response_code'] = '01';
            $requestData['response_msg'] ="transtype错误";
            $ret=JSONUtils::getResponseParam($requestData,JSONUtils::$responseErrorBodyNodes);
        }

        $ret = json_encode($ret);
        $responseMac = MD5($ret . JSONUtils::$CHECK_STR);
        Loggy::write('umspay',"回写给大华的信息是：" . $ret. "&mac=" .$responseMac);
        $response->setCharset('UTF-8');
        $response->setContent($ret. "&mac=" .$responseMac);
        return $response;
    }
}