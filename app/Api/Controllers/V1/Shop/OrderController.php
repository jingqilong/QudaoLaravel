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
     *             type="json",
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
        $info = [
            ['goods_id' => 1,'spec_relate_id' => 1,'number' => 1],
            ['goods_id' => 1,'spec_relate_id' => 2,'number' => 1],
        ];
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
}