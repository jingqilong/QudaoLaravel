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
}