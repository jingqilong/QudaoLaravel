<?php


namespace App\Services\Pay;


use App\Enums\PayMethodEnum;
use App\Library\UmsPay\Utils\UmsConstants;
use App\Repositories\MemberOrdersRepository;
use App\Services\BaseService;
use Ixudra\Curl\Facades\Curl;

class JsonNotifyService extends BaseService
{

    public function umsPay($request)
    {
        $mer_id     = env('STATIC_MER_ID',config('ums.STATIC_MER_ID')); //商户号
        $mac        = env('UMS_CHECK_STR',config('ums.UMS_CHECK_STR')); //签名
        if (!MemberOrdersRepository::exists(['order_no' => $request['busi_order_no']])){
            $this->setError('订单号不存在!');
            return false;
        }
        $ums_data   = [
            'order_no'          => $request['order_no'],
            'busi_order_no'     => $request['busi_order_no'],
            'mer_id'            => $mer_id,
            'payway'            => PayMethodEnum::getPayway($request['payway']),
            'cod'               => $request['cod'],
            'qrtype'            => PayMethodEnum::getScenario($request['qrtype']),
            'memo'              => $request['memo'],
            'orderDesc'         => $request['orderDesc'] ?? '',
            'employeeNo'        => $request['employeeNo'] ?? '01',
            'signType'          => PayMethodEnum::getEncryption($request['signType'],PayMethodEnum::SM3),
            'mac'               => $mac,
        ];
        //context={“header”:{“version”:”1.0”,”transtype”:”P003”,”employno”:”072001”,”termid”:”12345678”}}


        $result = Curl::to(config('ums.TEST_PAY_URL'))->withData($ums_data)->asJsonRequest()->get();
        if ($result['code'] != '00'){

        }
        if (!$ums_pay_prosperity = $this->umsPaySuccess($result)){

        }
    }




    /**
     * 银联支付接口
     * @param $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed|void
     */
    public function doPost($request)
    {
        if (empty($request['mac']) || empty($request['context'])){
            $res  = json_encode(['response_code' => '01','response_msg' => '缺少必要参数mac或者context']);
            $sign = md5($res . config('ums.CHECK_STR'));
            return Response($res . '&mac=' . $sign);
        }
        //验证签名，对收到的原始报文和秘钥进行md5加密
        $localMac = MD5($request['context'] . env('UMS_CHECK_STR',config('ums.CHECK_STR')));
        $res = [];
        #验证签名
        if ($localMac !== $request['mac']){
            $res  = json_encode(['response_code' => '01','response_msg' => 'MAC签名不一致']);
            $sign = md5($res . config('ums.CHECK_STR'));
            return Response($res . '&mac=' . $sign);
        }
        #判断返回码
        if ('00' !== $request['code']){
            $res  = json_encode(['response_code' => '01','response_msg' => $request['msg']]);
            $sign = md5($res . config('ums.CHECK_STR'));
            return Response($res . '&mac=' . $sign);
        }
        #判断操作类型
        try{
            if ('P033' === $request['transtype']){//支付通知
                //支付通知，商户自行处理,所需要的参数在map中取，参数key在repeustP033BodyNodes里面
                //此处更新数据库的动作省略....
                //组装响应的报文
                $bodyData = json_decode($request['repeustP033BodyNodes'],true);
                if (!$ums_pay_prosperity = $this->umsPaySuccess($bodyData)){
                    return $ums_pay_prosperity;
                }
                return $ums_pay_prosperity;
            }elseif ('P036' === $request['transtype']){//退款通知
                //退款通知，商户自行处理,所需要的参数在map中取，参数key在repeustP036BodyNodes里面
                //此处更新数据库的动作省略....
                //组装响应的报文
                $bodyData = json_decode($request['repeustP036BodyNodes'],true);
                if (!($ums_pay_prosperity = $this->umsPayRefund($bodyData))){
                    return $ums_pay_prosperity;
                }
                return $bodyData;
            }else{
                $res  = json_encode(['response_code' => '01','response_msg' => 'transtype错误']);
                $sign = md5($res . config('ums.CHECK_STR'));
                return Response($res . '&mac=' . $sign);
            }
        }catch (\Exception $e){
            $res  = json_encode(['response_code' => '01','response_msg' => '系统内部错误']);
            $sign = md5($res . config('ums.CHECK_STR'));
            return Response($res . '&mac=' . $sign);
        }
    }

    //支付成功逻辑
    public function umsPaySuccess($bodyData){
        return true;
    }

    //退款通知逻辑
    public function umsPayRefund($bodyData){
        return true;
    }



}