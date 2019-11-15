<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\GoodsCategoryService;
use App\Services\Shop\GoodsService;

class GoodsController extends ApiController
{
    public $goodsService;
    public $categoryService;

    /**
     * GoodsController constructor.
     * @param GoodsService $goodsService
     * @param GoodsCategoryService $goodsCategoryService
     */
    public function __construct(GoodsService $goodsService,GoodsCategoryService $goodsCategoryService)
    {
        parent::__construct();
        $this->goodsService     = $goodsService;
        $this->categoryService = $goodsCategoryService;
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

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_goods_list",
     *     tags={"商城"},
     *     summary="获取商品列表",
     *     operationId="get_goods_list",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索，【商品名、类别、规格】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="商品类别",
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
    public function getGoodsList(){
        $rules = [
            'category'          => 'integer',
            'status'            => 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'category.integer'      => '商品类别必须为整数',
            'status.integer'        => '上下架必须为整数',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->goodsList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/category_list",
     *     tags={"商城"},
     *     summary="获取商品分类列表",
     *     operationId="category_list",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getCategoryList(){
        $res = $this->categoryService->getHomeCategoryList();
        if ($res === false){
            return ['code' => 100,'message' => $this->categoryService->error];
        }
        return ['code' => 200, 'message' => $this->categoryService->message,'data' => $res];
    }
}