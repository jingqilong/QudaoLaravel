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
     *         name="category",
     *         in="query",
     *         description="商品类别",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
     *         name="labels",
     *         in="query",
     *         description="商品标签，使用逗号分隔， 【高端,美食】",
     *         required=false,
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
     *         name="stock",
     *         in="query",
     *         description="库存，无规格时必须填写",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="商品价格，单位：元，整数或两位小数",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="negotiable",
     *         in="query",
     *         description="是否面议，【默认 0无需 1面议】",
     *         required=true,
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
     *         name="score_categories",
     *         in="query",
     *         description="可抵扣积分类别串",
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
     *         name="gift_score",
     *         in="query",
     *         description="购买赠积分，默认10",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_exchange",
     *         in="query",
     *         description="是否展示到‘积分兑换’栏目，0否1是",
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
            'price'             => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'negotiable'        => 'required|integer',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'image_ids'         => 'required|regex:/^(\d+[,])*\d+$/',
            'labels'            => 'string',
            'stock'             => 'required|integer',
            'express_price'     => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'score_deduction'   => 'integer',
            'score_categories'  => 'regex:/^(\d+[,])*\d+$/',
            'gift_score'        => 'integer',
            'is_recommend'      => 'in:1,2',
            'status'            => 'required|in:1,2',
            'spec_json'         => 'json',
            'score_exchange'    => 'in:0,1',
        ];
        $messages = [
            'name.required'             => '商品名称不能为空',
            'category.required'         => '商品类别不能为空',
            'category.integer'          => '商品类别必须为整数',
            'negotiable.required'       => '面议类别不能为空',
            'negotiable.integer'        => '面议类别必须为整数',
            'price.regex'               => '商品价格必须为整数或两位小数',
            'labels.string'             => '商品标签必须为字符串',
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
            'score_exchange.in'         => '是否展示到‘积分兑换’栏目取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->addGoods($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/shop/delete_goods",
     *     tags={"商城后台"},
     *     summary="删除商品",
     *     operationId="delete_goods",
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
     *         name="id",
     *         in="query",
     *         description="商品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteGoods(){
        $rules = [
            'id'           => 'required|integer'
        ];
        $messages = [
            'id.required'      => '商品ID不能为空',
            'id.integer'       => '商品ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->deleteGoods($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/is_putaway_goods",
     *     tags={"商城后台"},
     *     summary="上下架商品",
     *     operationId="is_putaway_goods",
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
     *         name="id",
     *         in="query",
     *         description="商品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function isPutaway(){
        $rules = [
            'id'           => 'required|integer'
        ];
        $messages = [
            'id.required'      => '商品ID不能为空',
            'id.integer'       => '商品ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->isPutaway($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/edit_goods",
     *     tags={"商城后台"},
     *     summary="修改商品",
     *     operationId="edit_goods",
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
     *         name="id",
     *         in="query",
     *         description="商品ID",
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
     *         name="name",
     *         in="query",
     *         description="商品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="labels",
     *         in="query",
     *         description="商品标签，使用逗号分隔， 【高端,美食】",
     *         required=false,
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
     *         name="stock",
     *         in="query",
     *         description="库存，无规格时必须填写",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="商品价格，单位：元，整数或两位小数",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="negotiable",
     *         in="query",
     *         description="是否面议，【默认 0无需 1面议】",
     *         required=true,
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
     *         name="score_categories",
     *         in="query",
     *         description="可抵扣积分类别串",
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
     *         name="gift_score",
     *         in="query",
     *         description="购买赠积分，默认10",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_exchange",
     *         in="query",
     *         description="是否展示到‘积分兑换’栏目，0否1是",
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
     *         name="spec_json",
     *         in="query",
     *         description="规格，json对象字符串，",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="商品修改失败",),
     * )
     *
     */
    public function editGoods(){
        $rules = [
            'id'                => 'required|integer',
            'name'              => 'required',
            'category'          => 'required|integer',
            'price'             => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'negotiable'        => 'required|in:0,1',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'image_ids'         => 'required|regex:/^(\d+[,])*\d+$/',
            'stock'             => 'required|integer',
            'express_price'     => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'score_deduction'   => 'integer',
            'score_categories'  => 'regex:/^(\d+[,])*\d+$/',
            'gift_score'        => 'integer',
            'is_recommend'      => 'in:1,2',
            'status'            => 'required|in:1,2',
            'spec_json'         => 'json',
            'score_exchange'    => 'in:0,1',
        ];
        $messages = [
            'id.required'               => '商品ID不能为空',
            'id.integer'                => '商品ID必须为整数',
            'name.required'             => '商品名称不能为空',
            'category.required'         => '商品类别不能为空',
            'category.integer'          => '商品类别必须为整数',
            'price.regex'               => '商品价格必须为整数或两位小数',
            'negotiable.required'       => '价格类型不能为空',
            'negotiable.in'             => '价格类型不存在',
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
            'score_exchange.in'         => '是否展示到‘积分兑换’栏目取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->editGoods($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/goods_list",
     *     tags={"商城后台"},
     *     summary="获取商品列表",
     *     operationId="goods_list",
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
     *         name="status",
     *         in="query",
     *         description="上下架，1上架，2下架",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_deduction",
     *         in="query",
     *         description="积分可抵扣，0不可抵扣，1可抵扣",
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
    public function goodsList(){
        $rules = [
            'category'          => 'integer',
            'status'            => 'integer',
            'score_deduction'   => 'in:0,1',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'category.integer'      => '商品类别必须为整数',
            'status.integer'        => '上下架必须为整数',
            'score_deduction.in'    => '积分可抵扣取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->getGoodsList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_goods_detail",
     *     tags={"商城后台"},
     *     summary="获取商品详情",
     *     operationId="get_goods_detail",
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
     *         name="id",
     *         in="query",
     *         description="商品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function getGoodsDetail(){
        $rules = [
            'id'           => 'required|integer'
        ];
        $messages = [
            'id.required'      => '商品ID不能为空',
            'id.integer'       => '商品ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsService->getGoodsDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsService->error];
        }
        return ['code' => 200, 'message' => $this->goodsService->message,'data' => $res];
    }
}