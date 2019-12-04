<?php


namespace App\Api\Controllers\V1\Pay;


use App\Api\Controllers\ApiController;
use App\Services\Pay\WeChatPayService;

class WeChatPayController extends ApiController
{
    public $weChatPayService;

    /**
     * WeChatPayController constructor.
     * @param $weChatPayService
     */
    public function __construct(WeChatPayService $weChatPayService)
    {
        parent::__construct();
        $this->weChatPayService = $weChatPayService;
    }

    /**
     * 微信小程序微信支付接口
     *
     * @OA\Post(
     *     path="/api/v1/payments/we_chat_pay",
     *     tags={"支付模块"},
     *     summary="微信小程序微信支付下单接口",
     *     description="用于微信小程序支付下单,sang",
     *     operationId="we_chat_pay",
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
     *          name="token",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="用户token",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="order_no",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="订单号",
     *          required=true,
     *     ),
     *     @OA\Response(
     *          response="default",
     *          description="操作成功：code = 200，data = ['appId' => 'APPID','timeStamp' => '时间戳','nonceStr' => '随机字符串','package' => '数据包','signType' => '签名类型','sign' => '签名',]",
     *      )
     * )
     * )
     */
    public function weChatPay(){
        $rules = [
            'order_no'     => 'required|regex:/\d+$/',
        ];
        $message = [
            'order_no.required'     => '订单号不能为空',
            'order_no.regex'        => '订单号必须为纯数字',
        ];
        $Validate = $this->ApiValidate($rules, $message);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->weChatPayService->placeOrder($this->request);
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $result['message'], 'data' => $result['data']];
        }else{
            return ['code' => 100, 'message' => $result['message']];
        }
    }

    /**
     * 微信小程序微信支付接口
     *
     * @OA\Get(
     *     path="/api/v1/payments/get_jsapi_ticket",
     *     tags={"支付模块"},
     *     summary="微信微信获取授权签名",
     *     description="sang",
     *     operationId="get_jsapi_ticket",
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
     *          name="token",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="用户token",
     *          required=true,
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="url",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="当前页面url",
     *          required=true,
     *     ),
     *     @OA\Response(
     *          response="default",
     *          description="",
     *      )
     * )
     * )
     */
    public function getJsapiTicket(){
        $rules = [
            'url'     => 'required|url',
        ];
        $message = [
            'url.required'      => '当前页面url不能为空不能为空',
            'url.url'           => '当前页面url格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $message);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->weChatPayService->getJsapiTicket($this->request['url'],$this->request['code']);
        if($result == false) {
            return ['code' => 100, 'message' => $this->weChatPayService->error];
        }else{
            return ['code' => 200, 'message' => $this->weChatPayService->message,'data' => $result];
        }
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/payments/we_chat_pay_call_back",
     *     tags={"支付模块"},
     *     summary="微信小程序微信支付回调接口",
     *     description="用于微信小程序支付回调,sang",
     *     operationId="we_chat_pay_call_back",
     *   @OA\Response(
     *     response="default",
     *     description="操作成功：return_code = 'SUCCESS' , return_msg = 'OK'",
     *     @OA\Schema(
     *          @OA\Property(property="code",type="string"),
     *          @OA\Property(property="msg",type="string"),
     *          @OA\Property(property="data",type="array",
     *              @OA\Items(
     *                  @OA\Property(property="appId",type="string"),
     *                  @OA\Property(property="timeStamp",type="string"),
     *                  @OA\Property(property="nonceStr",type="string"),
     *                  @OA\Property(property="package",type="string"),
     *                  @OA\Property(property="signType",type="string"),
     *                  @OA\Property(property="sign",type="string"),
     *              )
     *          ),
     *      )
     * )
     * )
     */
    public function weChatPayCallBack(){
        $weChatPayService = new WeChatPayService();
        $weChatPayService->payCallBack();
    }
}