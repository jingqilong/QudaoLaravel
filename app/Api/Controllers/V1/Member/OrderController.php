<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Enums\SMSEnum;
use App\Services\Common\SmsService;
use App\Services\Member\MemberService;
use App\Services\Member\OrdersService;
use App\Services\Oa\EmployeeService;
use Illuminate\Http\JsonResponse;

class OrderController extends ApiController
{
    protected $ordersService;

    /**
     * TestApiController constructor.
     * @param OrdersService $ordersService
     */
    public function __construct(OrdersService $ordersService)
    {
        parent::__construct();
        $this->ordersService = $ordersService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/place_order",
     *     tags={"会员"},
     *     summary="支付下单",
     *     description="用于不需要实物的订单下单",
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
     *         description="订单类型，1、会员充值，2、...",
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
            'order_type' => 'required|integer|min:1',
        ];
        $messages = [
            'amount.required'       => '请输入订单金额',
            'amount.integer'        => '订单金额必须为整数',
            'amount.min'            => '订单金额不能少于0分',
            'order_type.required'   => '请输入订单类型',
            'order_type.integer'    => '订单类型必须为整数',
            'order_type.min'        => '订单类型不存在',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->ordersService->placeOrder($this->request['amount'],$this->request['order_type']);
        if (is_string($res)){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '下单成功！', 'data' => $res];
    }
}