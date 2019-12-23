<?php


namespace App\Api\Controllers\V1\Pay;

use App\Api\Controllers\ApiController;
use App\Services\Pay\UmsPayService;

class UmsPayController extends ApiController
{
    public $umsPayService;

    /**
     * WeChatPayController constructor.
     * @param $umsPayService
     */
    public function __construct(UmsPayService $umsPayService)
    {
        parent::__construct();
        $this->umsPayService = $umsPayService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_create_order",
     *     tags={"支付模块"},
     *     summary="银联支付下单",
     *     description="sang" ,
     *     operationId="ums_create_order",
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
     *         response=200,
     *         description="成功"
     *     ),
     * )
     *
     */
    public function createOrder(){
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
        $result = $this->umsPayService->createOrder($this->request);
        if ($result == false){
            return ['code' => 100, 'message' => $this->umsPayService->error];
        }
        return ['code' => 200, 'message' => $this->umsPayService->message, 'data' => $result];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_query_clear_date",
     *     tags={"支付模块"},
     *     summary="queryClearDate",
     *     description="sang" ,
     *     operationId="ums_query_clear_date",
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
     *     @OA\Parameter(
     *          in="query",
     *          name="clear_date",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="清算日期",
     *          required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryClearDate(){
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
        $response = $this->umsPayService->queryClearDate($this->request);
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_query_trans_date",
     *     tags={"支付模块"},
     *     summary="queryTransDate",
     *     description="sang" ,
     *     operationId="ums_query_trans_date",
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
     *     @OA\Parameter(
     *          in="query",
     *          name="trans_date",
     *          @OA\Schema(
     *                  type="string",
     *              ),
     *          description="交易日期",
     *          required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryTransDate(){
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

        $response = $this->umsPayService->queryTransDate($this->request);
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_query_by_system_code",
     *     tags={"支付模块"},
     *     summary="queryBySystemCode",
     *     description="sang" ,
     *     operationId="ums_query_by_system_code",
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
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryBySystemCode(){
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

        $response = $this->umsPayService->queryBySystemCode($this->request);
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_refund",
     *     tags={"支付模块"},
     *     summary="refund",
     *     description="sang" ,
     *     operationId="ums_refund",
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
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function refund(){
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
        $response = $this->umsPayService->refund($this->request);
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/payments/ums_pay_call_back",
     *     tags={"支付模块"},
     *     summary="银联支付回调接口",
     *     description="用于银联支付支付回调,bardo",
     *     operationId="ums_pay_call_back",
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
     *   )
     * )
     */
    public function umsPayCallBack(){
        return $this->umsPayService->payCallBack($this->request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/ums_query_order_status",
     *     tags={"支付模块"},
     *     summary="查询订单支付状态",
     *     description="bardo" ,
     *     operationId="umsQueryOrderStatus",
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
     *         response=200,
     *         description="成功: ['code' => 200, 'message' => '查询成功', 'data' => [1，待付款，2，已付款，3，未付款（付款失败），4，已关闭（已取消）] ];]",
     *     ),
     * )
     *
     */
    public function umsQueryOrderStatus(){
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
        $response = $this->umsPayService->umsQueryOrderStatus($this->request);
        if(false === $response){
            $response['status'] = 5;
            $response['order_type'] = -1;
            return ['code' => 200, 'message' => '订单信息不存在！请返回重新下单！', 'data' => $response];
        }
        $response['status'] += 1;
        if(0 === $response['status']){
            return ['code' => 200, 'message' => '等待支付，请在浏览器中支付！', 'data' => $response];
        }
        return ['code' => 200, 'message' => '查询成功', 'data' => $response];
    }
}