<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\OrdersService;
use App\Services\Member\TradesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{
    protected $ordersService;
    protected $tradeService;

    /**
     * TestApiController constructor.
     * @param OrdersService $ordersService
     * @param TradesService $tradesService
     */
    public function __construct(OrdersService $ordersService,TradesService $tradesService)
    {
        parent::__construct();
        $this->ordersService    = $ordersService;
        $this->tradeService     = $tradesService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/place_order",
     *     tags={"会员"},
     *     summary="支付下单",
     *     description="用于不需要实物的订单下单,sang",
     *     operationId="place_order",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="金额，单位：分",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_type",
     *         in="query",
     *         description="订单类型，1、会员充值...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="下单失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function placeOrder()
    {
        $rules = [
            'amount'   => 'required|integer|min:0',
            'order_type' => 'required|in:1',
        ];
        $messages = [
            'amount.required'       => '请输入订单金额',
            'amount.integer'        => '订单金额必须为整数',
            'amount.min'            => '订单金额不能少于0分',
            'order_type.required'   => '请输入订单类型',
            'order_type.in'         => '订单类型不存在'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->ordersService->placeOrder($member->id,$this->request['amount'],$this->request['order_type']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->ordersService->error];
        }
        return ['code' => 200, 'message' => $this->ordersService->message, 'data' => ['order_no' => $res]];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_order_list",
     *     tags={"会员后台"},
     *     summary="获取会员所有订单列表",
     *     description="sang",
     *     operationId="get_order_list",
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
     *         name="order_no",
     *         in="query",
     *         description="订单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="会员ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_type",
     *         in="query",
     *         description="订单类型，1、会员充值，2、参加活动，3、精选生活，4、商城...",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_type",
     *         in="query",
     *         description="抵扣积分类型",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="订单状态，0，待付款，1，已付款，2，未付款（付款失败），3，已关闭（已取消）",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getOrderList(){
        $rules = [
            'member_id'     => 'integer',
            'order_type'    => 'in:1,2,3,4',
            'score_type'    => 'integer',
            'status'        => 'in:0,1,2,3',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'member_id.integer'     => '会员ID必须为整数',
            'order_type.in'         => '订单类型不存在',
            'score_type.integer'    => '抵扣积分类型必须为整数',
            'status.in'             => '订单状态不存在',
            'page.integer'          => '页数必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->ordersService->getOrderList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->ordersService->error];
        }
        return ['code' => 200, 'message' => $this->ordersService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_trade_list",
     *     tags={"会员后台"},
     *     summary="获取会员所有交易列表",
     *     description="sang",
     *     operationId="get_trade_list",
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
     *         name="trade_no",
     *         in="query",
     *         description="本地交易号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="transaction_no",
     *         in="query",
     *         description="第三方交易号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="pay_user_id",
     *         in="query",
     *         description="付款方ID，0为系统",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="payee_user_id",
     *         in="query",
     *         description="收款方ID，0为系统",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="fund_flow",
     *         in="query",
     *         description="资金流向：+进账，-出账",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="trade_method",
     *         in="query",
     *         description="交易方式：1微信支付，2，积分支付，3，银联支付",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="交易状态，0、正在交易，1、成功，2、失败",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getTradeList(){
        $rules = [
            'pay_user_id'   => 'integer',
            'payee_user_id' => 'integer',
            'fund_flow'     => 'in:+,-',
            'trade_method'  => 'in:1,2,3',
            'status'        => 'in:0,1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'pay_user_id.integer'   => '付款方ID必须为整数',
            'payee_user_id.integer' => '收款方ID必须为整数',
            'fund_flow.in'          => '资金流向有误',
            'trade_method.in'       => '交易方式不存在',
            'status.in'             => '交易状态不存在',
            'page.integer'          => '页数必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->tradeService->getTradeList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->tradeService->error];
        }
        return ['code' => 200, 'message' => $this->tradeService->message, 'data' => $res];
    }
}