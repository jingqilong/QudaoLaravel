<?php
namespace App\Api\Controllers\V1\Shop;

use App\Api\Controllers\ApiController;
use App\Services\Shop\ShopInventoryService;

class OaShopInventoryController extends ApiController
{
    public $shopInventoryService;

    /**
     * OrderController constructor.
     * @param $shopInventoryService
     */
    public function __construct(ShopInventoryService $shopInventoryService)
    {
        parent::__construct();
        $this->shopInventoryService = $shopInventoryService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_shop_invertor_list",
     *     tags={"商城后台"},
     *     summary="获取商品库存列表",
     *     description="sang" ,
     *     operationId="get_shop_invertor_list",
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
     *         name="name",
     *         in="query",
     *         description="搜索关键字，【商品名称】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         description="商品ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="spec_id",
     *         in="query",
     *         description="规格ID",
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
    public function getShopInventorList(){
        $rules = [
            'goods_id'          => 'integer',
            'spec_id'           => 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'goods_id.integer'            => '商品ID必须为整数',
            'spec_id.integer'             => '规格ID必须为整数',
            'page.integer'                => '页面序号必须为整数',
            'page_num.integer'            => '每页记录数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->shopInventoryService->getInventoryList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->shopInventoryService->error];
        }
        return ['code' => 200, 'message' => $this->shopInventoryService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/change_inventor",
     *     tags={"商城后台"},
     *     summary="修改库存",
     *     operationId="change_inventor",
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
     *         name="goods_id",
     *         in="query",
     *         description="商品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="spec_id",
     *         in="query",
     *         description="规格ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="库存数量",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function changeInventor(){
        $rules = [
            'goods_id'          => 'integer',
            'spec_id'           => 'integer',
            'amount'            => 'integer',
        ];
        $messages = [
            'goods_id.integer'            => '商品ID必须为整数',
            'spec_id.integer'             => '规格ID必须为整数',
            'amount.integer'              => '库存数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->shopInventoryService->createInventor($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->shopInventoryService->error];
        }
        return ['code' => 200, 'message' => $this->shopInventoryService->message];
    }
}