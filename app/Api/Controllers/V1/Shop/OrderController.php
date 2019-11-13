<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\OrderRelateService;

class OrderController extends ApiController
{
    public $orderRelateService;

    /**
     * OrderController constructor.
     * @param $orderRelateService
     */
    public function __construct(OrderRelateService $orderRelateService)
    {
        parent::__construct();
        $this->orderRelateService = $orderRelateService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_place_order_detail",
     *     tags={"商城"},
     *     summary="获取下单详情",
     *     description="sang" ,
     *     operationId="get_place_order_detail",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="goods_json",
     *         in="query",
     *         description="商品json信息，",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getPlaceOrderDetail(){
//        $info = [
//            ['goods_id' => 1,'spec_relate_id' => 1,'number' => 1],
//            ['goods_id' => 1,'spec_relate_id' => 2,'number' => 1],
//        ];
        $rules = [
            'goods_json'    => 'required|json'
        ];
        $messages = [
            'goods_json.required'       => '商品json信息不能为空',
            'goods_json.json'           => '商品json信息必须为json格式'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->getPlaceOrderDetail($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/submit_order",
     *     tags={"商城"},
     *     summary="提交订单",
     *     description="sang" ,
     *     operationId="submit_order",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address_id",
     *         in="query",
     *         description="收货地址ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="goods_json",
     *         in="query",
     *         description="商品json信息，",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_type",
     *         in="query",
     *         description="邮寄方式，1收费邮寄、2快递到付、3上门自提",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_deduction",
     *         in="query",
     *         description="抵扣积分",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_type",
     *         in="query",
     *         description="抵扣积分类别",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="下单失败",
     *     ),
     * )
     *
     */
    public function submitOrder(){
        $rules = [
            'address_id'        => 'required|integer',
            'goods_json'        => 'required|json',
            'express_type'      => 'required|in:1,2,3',
            'score_deduction'   => 'integer',
            'score_type'        => 'integer',
        ];
        $messages = [
            'address_id.required'       => '收货地址不能为空',
            'address_id.integer'        => '收货地址ID必须为整数',
            'goods_json.required'       => '商品信息不能为空',
            'goods_json.json'           => '商品信息必须为json格式',
            'express_type.required'     => '邮寄方式不能为空',
            'express_type.in'           => '邮寄方式不存在',
            'score_deduction.in'        => '抵扣积分必须为整数',
            'score_type.in'             => '抵扣积分类别必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->submitOrder($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }
}