<?php


namespace App\Library\UmsPay\Notify;


use App\Library\UmsPay\Utils\UmsConstants;
use Illuminate\Http\Response;

class JsonNotify
{
    public function doPost($request,$response)
    {
        if (empty($request['mac']) || empty($request['context'])){
            return false;
        }
        //验证签名，对收到的原始报文和秘钥进行md5加密
        $localMac = MD5($request['context'] . UmsConstants::CHECK_STR);
        $res = [];
        #验证签名
        if ($localMac !== $request['mac']){
            $res  = json_encode(['response_code' => '01','response_msg' => 'MAC签名不一致']);
            $sign = md5($res . UmsConstants::CHECK_STR);
            return Response($res . '&mac=' . $sign);
        }
        #判断返回码
        if ('00' !== $request['code']){
            $res  = json_encode(['response_code' => '01','response_msg' => $request['msg']]);
            $sign = md5($res . UmsConstants::CHECK_STR);
            return Response($res . '&mac=' . $sign);
        }
        #判断操作类型
        try{
            if ('P033' === $request['transtype']){//支付通知
                //支付通知，商户自行处理,所需要的参数在map中取，参数key在repeustP033BodyNodes里面
                //此处更新数据库的动作省略....
                //组装响应的报文
                $bodyData = json_decode($request['repeustP033BodyNodes'],true);
                return $bodyData;
            }elseif ('P036' === $request['transtype']){//退款通知
                //退款通知，商户自行处理,所需要的参数在map中取，参数key在repeustP036BodyNodes里面
                //此处更新数据库的动作省略....
                //组装响应的报文
                $bodyData = json_decode($request['repeustP036BodyNodes'],true);
                return $bodyData;
            }else{
                $res  = json_encode(['response_code' => '01','response_msg' => 'transtype错误']);
                $sign = md5($res . UmsConstants::CHECK_STR);
                return Response($res . '&mac=' . $sign);
            }
        }catch (\Exception $e){
            $res  = json_encode(['response_code' => '01','response_msg' => '系统内部错误']);
            $sign = md5($res . UmsConstants::CHECK_STR);
            return Response($res . '&mac=' . $sign);
        }
    }
}