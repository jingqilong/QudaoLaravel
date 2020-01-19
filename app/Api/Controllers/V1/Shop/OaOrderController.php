<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\NegotiableOrderService;
use App\Services\Shop\OrderRelateService;

class OaOrderController extends ApiController
{
    public $orderRelateService;
    public $negotiableOrderService;

    /**
     * OrderController constructor.
     * @param OrderRelateService $orderRelateService
     * @param NegotiableOrderService $negotiableOrderService
     */
    public function __construct(OrderRelateService $orderRelateService,NegotiableOrderService $negotiableOrderService)
    {
        parent::__construct();
        $this->orderRelateService = $orderRelateService;
        $this->negotiableOrderService = $negotiableOrderService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_shop_order_list",
     *     tags={"商城后台"},
     *     summary="获取商城订单列表",
     *     description="sang" ,
     *     operationId="get_shop_order_list",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字，【用户姓名、用户手机号、收件人姓名、收件人手机号、收货备注】",
     *         required=false,
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
     *         name="express_number",
     *         in="query",
     *         description="快递单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_no",
     *         in="query",
     *         description="订单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_company_id",
     *         in="query",
     *         description="快递公司ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="receive_method",
     *         in="query",
     *         description="收货方式，默认1收费邮寄、2快递到付、3上门自提",
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
    public function getShopOrderList(){
        $rules = [
            'status'            => 'in:0,1,2,3,4',
            'receive_method'    => 'in:1,2,3',
            'express_company_id'=> 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'status.in'                 => '订单状态不存在',
            'receive_method.in'         => '收货方式不存在',
            'express_company_id.integer'=> '快递公司ID必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->getShopOrderList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_negotiable_order_list",
     *     tags={"商城后台"},
     *     summary="获取面议订单列表",
     *     description="sang" ,
     *     operationId="get_negotiable_order_list",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字，【用户姓名、用户手机号、收件人姓名、收件人手机号、收货备注】",
     *         required=false,
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
     *         name="express_number",
     *         in="query",
     *         description="快递单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_no",
     *         in="query",
     *         description="订单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_company_id",
     *         in="query",
     *         description="快递公司ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="receive_method",
     *         in="query",
     *         description="收货方式，默认1收费邮寄、2快递到付、3上门自提",
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
    public function getNegotiableOrderList(){
        $rules = [
            'status'            => 'in:0,1,2,3,4',
            'receive_method'    => 'in:1,2,3',
            'express_company_id'=> 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'status.in'                 => '订单状态不存在',
            'receive_method.in'         => '收货方式不存在',
            'express_company_id.integer'=> '快递公司ID必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->negotiableOrderService->getNegotiableOrderList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_order_detail",
     *     tags={"商城后台"},
     *     summary="获取订单详情",
     *     description="sang" ,
     *     operationId="get_order_detail",
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
     *         description="OA token",
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
    public function getOrderDetail(){
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
        $res = $this->orderRelateService->orderDetail($this->request['order_relate_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_negotiable_order_details",
     *     tags={"商城后台"},
     *     summary="获取面议订单详情",
     *     description="sang" ,
     *     operationId="get_negotiable_order_details",
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
     *         description="OA token",
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
    public function getNegotiableOrderDetails(){
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
        $res = $this->negotiableOrderService->getNegotiableOrderDetails($this->request['order_relate_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/shop/shipment",
     *     tags={"商城后台"},
     *     summary="发货",
     *     description="sang" ,
     *     operationId="shipment",
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
     *         description="OA token",
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
     *     @OA\Parameter(
     *         name="express_company_id",
     *         in="query",
     *         description="快递公司ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_number",
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
    public function shipment(){
        $rules = [
            'order_relate_id'       => 'required|integer',
            'express_company_id'    => 'required|integer',
            'express_number'        => 'required',
        ];
        $messages = [
            'order_relate_id.required'      => '订单关联ID不能为空',
            'order_relate_id.integer'       => '订单关联ID必须为整数',
            'express_company_id.required'   => '快递公司ID不能为空',
            'express_company_id.integer'    => '快递公司ID必须为整数',
            'express_number.required'       => '快递单号不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->orderRelateService->shipment($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/set_negotiable_order_amount",
     *     tags={"商城后台"},
     *     summary="录入面议订单金额",
     *     description="sang" ,
     *     operationId="set_negotiable_order_amount",
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
     *         description="OA token",
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
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="总金额（不含邮费）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_price",
     *         in="query",
     *         description="邮费，不填写则使用下单时的邮费",
     *         required=false,
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
    public function setNegotiableOrderAmount(){
        $rules = [
            'order_relate_id'   => 'required|integer',
            'amount'            => 'required|amount',
            'express_price'     => 'amount',
        ];
        $messages = [
            'order_relate_id.required'  => '订单关联ID不能为空',
            'order_relate_id.integer'   => '订单关联ID必须为整数',
            'amount.required'           => '总金额不能为空',
            'amount.amount'             => '总金额格式有误',
            'express_price.amount'      => '邮费格式有误'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->negotiableOrderService->setNegotiableOrderAmount($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->orderRelateService->error];
        }
        return ['code' => 200, 'message' => $this->orderRelateService->message];
    }
}