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