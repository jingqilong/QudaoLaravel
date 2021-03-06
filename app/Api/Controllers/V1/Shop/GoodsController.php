<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\GoodsCategoryService;
use App\Services\Shop\GoodsService;
use App\Services\Shop\GoodsSpecRelateService;

class GoodsController extends ApiController
{
    public $goodsService;
    public $categoryService;
    public $goodsSpecRelateService;

    /**
     * GoodsController constructor.
     * @param GoodsService $goodsService
     * @param GoodsCategoryService $goodsCategoryService
     * @param GoodsSpecRelateService $goodsSpecRelateService
     */
    public function __construct(GoodsService $goodsService,
                                GoodsCategoryService $goodsCategoryService,
                                GoodsSpecRelateService $goodsSpecRelateService)
    {
        parent::__construct();
        $this->goodsService             = $goodsService;
        $this->categoryService          = $goodsCategoryService;
        $this->goodsSpecRelateService   = $goodsSpecRelateService;
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
     *         name="price_sort",
     *         in="query",
     *         description="价格排序，1价格从高到低，2价格从低到高",
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
            'price_sort'        => 'in:1,2',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'category.integer'      => '商品类别必须为整数',
            'price_sort.integer'    => '价格排序取值有误',
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

    /**
     * @OA\Get(
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


    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_goods_ad_details",
     *     tags={"商城"},
     *     summary="广告用户获取商品详情",
     *     description="jing" ,
     *     operationId="get_goods_ad_details",
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
    public function getGoodsAdDetails(){
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
        $res = $this->goodsService->getGoodsAdDetails($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_goods_spec",
     *     tags={"商城"},
     *     summary="获取商品规格",
     *     description="sang" ,
     *     operationId="get_goods_spec",
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
    public function getGoodsSpec(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'     => '请输入商品id',
            'id.integer'      => '商品id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->goodsSpecRelateService->getGoodsSpec($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->goodsSpecRelateService->error];
        }
        return ['code' => 200, 'message' => $this->goodsSpecRelateService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_goods_spec_list",
     *     tags={"商城"},
     *     summary="获取商品规格",
     *     description="sang" ,
     *     operationId="get_goods_spec_list",
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
    public function getGoodsSpecList(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'     => '请输入商品id',
            'id.integer'      => '商品id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->goodsSpecRelateService->getGoodsSpecList($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->goodsSpecRelateService->error];
        }
        return ['code' => 200, 'message' => $this->goodsSpecRelateService->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_shop_random_count",
     *     tags={"商城"},
     *     summary="随机获取推荐的商品",
     *     description="sang" ,
     *     operationId="get_shop_random_count",
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
     *         name="count",
     *         in="query",
     *         description="获取商品推荐的个数",
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
    public function getShopRandomCount(){
        $rules = [
            'count'  => 'required|integer',
        ];
        $messages = [
            'count.required' => '获取商品推荐数量不能为空',
            'count.integer'  => '获取商品推荐数量类型不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->goodsService->getShopRandomCount($this->request['count'],[],[]);
        if ($res === false){
            return ['code' => 100, 'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }
}