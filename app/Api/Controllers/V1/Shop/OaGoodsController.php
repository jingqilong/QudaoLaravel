<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\GoodsService;

class OaGoodsController extends ApiController
{

    public $goodsService;

    /**
     * OaGoodsController constructor.
     * @param $goodsService
     */
    public function __construct(GoodsService $goodsService)
    {
        parent::__construct();
        $this->goodsService = $goodsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/add_goods",
     *     tags={"商城后台"},
     *     summary="添加商品",
     *     operationId="add_goods",
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
     *         description="商品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="商品类别",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="商品价格，单位：元，整数或两位小数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="details",
     *         in="query",
     *         description="详情介绍",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="banner_ids",
     *         in="query",
     *         description="banner图ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="详情图ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="stock",
     *         in="query",
     *         description="库存，无规格时必须填写",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="express_price",
     *         in="query",
     *         description="快递费，单位：元",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_deduction",
     *         in="query",
     *         description="可抵扣积分",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_categories",
     *         in="query",
     *         description="可抵扣积分类别串",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gift_score",
     *         in="query",
     *         description="购买赠积分，默认10",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="推荐，1推荐，2不推荐",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1上架，2下架",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="spec_json",
     *         in="query",
     *         description="规格，json对象字符串，",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="商品添加失败",),
     * )
     *
     */
    public function addGoods(){
        $rules = [
            'name'              => 'required',
            'category'          => 'required|integer',
            'price'             => 'required|regex://',
            'banner_ids'        => 'required|regex://',
            'image_ids'         => 'required|regex://',
            'stock'             => 'required|integer',
            'express_price'     => 'regex://',
            'score_deduction'   => 'integer',
            'score_categories'  => 'regex://',
            'gift_score'        => 'integer',
            'is_recommend'      => 'in:1,2',
            'status'            => 'required|in:1,2',
            'spec_json'         => 'json',
        ];
        $messages = [
            'name.required'             => '商品名称不能为空',
            'category.required'         => '商品类别不能为空',
            'category.integer'          => '商品类别必须为整数',
            'price.required'            => '商品价格不能为空',
            'price.regex'               => '商品价格必须为整数或两位小数',
            'banner_ids.required'       => 'banner图不能为空',
            'banner_ids.regex'          => 'banner图ID串格式有误',
            'image_ids.required'        => '商品详情图不能为空',
            'image_ids.regex'           => '商品详情图ID串格式有误',
            'stock.required'            => '库存不能为空',
            'stock.integer'             => '库存必须为整数',
            'express_price.regex'       => '快递费必须为整数或两位小数',
            'score_deduction.integer'   => '可抵扣积分必须为整数',
            'score_categories.regex'    => '可抵扣积分类别串格式有误',
            'gift_score.integer'        => '购买赠积分必须为整数',
            'is_recommend.in'           => '推荐取值有误',
            'status.required'           => '状态不能为空',
            'status.in'                 => '状态取值有误',
            'spec_json.json'            => '商品规格必须为json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->addGoods($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }

    public function deleteGoods(){}

    public function editGoods(){}

    public function goodsList(){}
}