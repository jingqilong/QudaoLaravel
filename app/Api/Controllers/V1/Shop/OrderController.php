<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\OrderRelateService;
use Illuminate\Support\Facades\Auth;

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
     *     @OA\Parameter(
     *         name="remarks",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
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

    /**
     * @OA\Post(
     *     path="/api/v1/shop/goods_receiving",
     *     tags={"商城"},
     *     summary="确认收货",
     *     description="sang" ,
     *     operationId="goods_receiving",
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
     *         name="order_relate_id",
     *         in="query",
     *         description="订单关联ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="确认收货失败",
     *     ),
     * )
     *
     */
    public function goodsReceiving(){
        $rules = [
            'order_relate_id'       => 'required|integer'
        ];
        $messages = [
            'order_relate_id.required'  => '订单关联ID不能为空',
            'order_relate_id.integer'   => '订单关联ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->goodsReceiving($this->request['order_relate_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/shop/cancel_order",
     *     tags={"商城"},
     *     summary="取消订单",
     *     description="sang" ,
     *     operationId="cancel_order",
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
     *         name="order_relate_id",
     *         in="query",
     *         description="订单关联ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="取消订单失败",
     *     ),
     * )
     *
     */
    public function cancelOrder(){
        $rules = [
            'order_relate_id'       => 'required|integer'
        ];
        $messages = [
            'order_relate_id.required'  => '订单关联ID不能为空',
            'order_relate_id.integer'   => '订单关联ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->cancelOrder($this->request['order_relate_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_my_order_list",
     *     tags={"商城"},
     *     summary="获取我的订单列表",
     *     description="sang" ,
     *     operationId="get_my_order_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *         name="status",
     *         in="query",
     *         description="订单状态，0已取消，1待支付，2待发货（已支付），3已发货（待收货），4已收货",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getMyOrderList(){
        $rules = [
            'status'        => 'in:0,1,2,3,4',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'status.in'             => '订单状态不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->getMyOrderList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/order_detail",
     *     tags={"商城"},
     *     summary="获取订单详情",
     *     description="sang" ,
     *     operationId="order_detail",
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
     *         name="order_relate_id",
     *         in="query",
     *         description="订单关联ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function orderDetail(){
        $rules = [
            'order_relate_id'       => 'required|integer'
        ];
        $messages = [
            'order_relate_id.required'  => '订单关联ID不能为空',
            'order_relate_id.integer'   => '订单关联ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->orderRelateService->orderDetail($this->request['order_relate_id'],$member->m_id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/get_order_express_details",
     *     tags={"商城"},
     *     summary="用户根据订单号获取物流状态",
     *     description="jing" ,
     *     operationId="get_order_express_details",
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
     *         name="code",
     *         in="query",
     *         description="快递公司编码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="快递单号",
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
    public function getOrderExpressDetails(){
        $rules = [
            'code'      => 'required|string',
            'number'    => 'required|string',
        ];
        $messages = [
            'code.required'     => '请输入快递公司编码',
            'code.string'       => '快递公司格式错误',
            'number.required'   => '请输入快递单号',
            'number.string'     => '快递单号格式错误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->getOrderExpressDetails($this->request['code'],$this->request['number']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }
}