<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\GoodsService;

class GoodsController extends ApiController
{
    public $goodsService;

    /**
     * GoodsController constructor.
     * @param $goodsService
     */
    public function __construct(GoodsService $goodsService)
    {
        parent::__construct();
        $this->goodsService = $goodsService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_home",
     *     tags={"商城"},
     *     summary="获取首页",
     *     description="sang" ,
     *     operationId="get_home",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getHome(){
        $res = $this->goodsService->getHome();
        if ($res){
            return ['code' => 200,'message' => $this->goodsService->message,'data' => $res];
        }
        return ['code' => 100,'message' => $this->goodsService->error];
    }

    public function getGoodsList(){

    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/get_goods_details",
     *     tags={"商城"},
     *     summary="用户获取商品详情",
     *     description="jing" ,
     *     operationId="get_goods_details",
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
     *         description="成员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="商品id",
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
    public function getGoodsDetails(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'     => '请输入商品id',
            'id.integer'      => '商品id不是整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->goodsService->getGoodsDetailsById($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }
}