<?php


namespace App\Api\Controllers\V1\Pay;


use App\Api\Controllers\ApiController;
use App\Services\Pay\JsonNotifyService;

class UmsPayController extends ApiController
{
    public $JsonNotifyService;

    /**
     * UmsPayController constructor.
     * @param $JsonNotifyService
     */
    public function __construct(JsonNotifyService $JsonNotifyService)
    {
        parent::__construct();
        $this->JsonNotifyService = $JsonNotifyService;
    }

    /**
     * 微信小程序微信支付接口
     *
     * @OA\Post(
     *     path="/api/v1/payments/ums_pay",
     *     tags={"支付模块"},
     *     summary="银联支付",
     *     description="银联支付jing",
     *     operationId="ums_pay",
     *     @OA\Parameter(
     *     in="query",
     *     name="sign",
     *     @OA\Schema(
     *             type="string",
     *         ),
     *          description="签名",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="order_no",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="支付单号",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="busi_order_no",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="商户业务订单号",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="cod",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="支付金额",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="qrtype",
     *          @OA\Schema(
     *                  type="integer",
     *              ),
     *          description="使用场景【 1.h5 手机 浏览器使用  2.微信支付宝云闪付 app 内的浏览器中进行支付;】 ",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="payway",
     *          @OA\Schema(
     *                  type="integer",
     *              ),
     *          description="支付方式【1,银联在线(云闪付)】",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="memo",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="备注",
     *          required=false,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="orderDesc",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="订单备注",
     *          required=false,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="signType",
     *          @OA\Schema(
     *                  type="integer",
     *              ),
     *          description="签名方式【取值范围:1.MD5 2.SHA256 3.SM3 不传递默认使用 3】",
     *          required=false,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="employeeNo",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="操作员工号【不传值默认01】",
     *          required=false,
     *     ),
     *     @OA\Response(
     *          response="default",
     *          description="操作成功：code = 200，data = ['appId' => 'APPID','timeStamp' => '时间戳','nonceStr' => '随机字符串','package' => '数据包','signType' => '签名类型','sign' => '签名',]",
     *      )
     * )
     * )
     */
    public function umsPay(){
        $rules = [
            'order_no'          => 'required',
            'busi_order_no'     => 'required|regex:/\d+$/',
            'cod'               => 'required',
            'payway'            => 'required|in:1',
            'qrtype'            => 'required|in:1,2',
            'integer'           => 'in:1,2,3',
        ];
        $message = [
            'order_no.required'             => '订单号不能为空',
            'busi_order_no.required'        => '商户业务订单号不能为空',
            'busi_order_no.regex'           => '商户业务订单号必须为纯数字',
            'cod.required'                  => '支付金额不能为空',
            'payway.required'               => '支付方式不能为空',
            'payway.in'                     => '支付方式不存在',
            'qrtype.required'               => '支付场景不能为空',
            'qrtype.in'                     => '支付场景不存在',
            'integer.in'                    => '加密方法不存在',
        ];
        $Validate = $this->ApiValidate($rules, $message);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->JsonNotifyService->umsPay($this->request);
        return $result;
        if($result  != false) {
            return ['code' => 200, 'message' => $result['message'], 'data' => $result['data']];
        }else{
            return ['code' => 100, 'message' => $result['message']];
        }
    }







    /**
     * 微信小程序微信支付接口
     *
     * @OA\Post(
     *     path="/api/v1/payments/ums_pay_call_back",
     *     tags={"支付模块"},
     *     summary="银联支付回调接口",
     *     description="银联支付回调接口,jing",
     *     operationId="ums_pay_call_back",
     *     @OA\Parameter(
     *     in="query",
     *     name="sign",
     *     @OA\Schema(
     *             type="string",
     *         ),
     *          description="签名",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="mac",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="数据签名 mac",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="code",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="状态码 code【 00,01,02...】",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="context",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="返回的数据json对象",
     *          required=true,
     *     ),
     *     @OA\Response(
     *          response="default",
     *          description="操作成功：code = 200，data = ['appId' => 'APPID','timeStamp' => '时间戳','nonceStr' => '随机字符串','package' => '数据包','signType' => '签名类型','sign' => '签名',]",
     *      )
     * )
     * )
     */
    public function UmsPayCallBack()
    {
        return $this->JsonNotifyService->doPost($this->request);
    }
}