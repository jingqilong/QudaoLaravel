<?php

namespace App\Api\Controllers\V1\Prime;

use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantProductsService;
use Illuminate\Support\Facades\Auth;

class OaProductPrimeController extends ApiController
{
    protected $productsService;

    /**
     * TestApiController constructor.
     * @param MerchantProductsService $merchantProductsService
     */
    public function __construct(MerchantProductsService $merchantProductsService)
    {
        parent::__construct();
        $this->productsService = $merchantProductsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/add_product",
     *     tags={"精选生活OA后台"},
     *     summary="添加产品",
     *     description="sang" ,
     *     operationId="add_product",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="merchant_id",
     *         in="query",
     *         description="商户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="产品标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="产品描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="产品价格(单位：元)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="产品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐，默认不推荐，1推荐，2不推荐，推荐则显示在商铺详情中",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addProduct()
    {
        $rules = [
            'merchant_id'   => 'required|integer',
            'title'         => 'required',
            'price'         => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'is_recommend'  => 'in:1,2',
        ];
        $messages = [
            'merchant_id.required'  => '商户ID不能为空',
            'merchant_id.integer'   => '商户ID必须为整数',
            'title.required'        => '请输入产品标题',
            'price.required'        => '请输入产品价格',
            'price.regex'           => '产品价格格式有误，应为整数或两位小数',
            'image_ids.required'    => '请传入产品图片，至少一张',
            'image_ids.regex'       => '产品图片ID格式有误',
            'is_recommend.in'       => '推荐字段取值有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->productsService->addProduct($this->request,$this->request['merchant_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->productsService->error];
        }
        return ['code' => 200, 'message' => $this->productsService->message];
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/prime/delete_product",
     *     tags={"精选生活OA后台"},
     *     summary="删除产品",
     *     description="sang" ,
     *     operationId="delete_product",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="产品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function deleteProduct()
    {
        $rules = [
            'id'         => 'required|integer',
        ];
        $messages = [
            'id.required'       => '产品ID不能为空',
            'id.integer'        => '产品ID必须为整数'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->productsService->deleteProduct($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->productsService->error];
        }
        return ['code' => 200, 'message' => $this->productsService->message];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/prime/edit_product",
     *     tags={"精选生活OA后台"},
     *     summary="修改产品",
     *     description="sang" ,
     *     operationId="edit_product",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="产品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="产品标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="产品描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="产品价格(单位：元)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="产品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐，默认不推荐，1推荐，2不推荐，推荐则显示在商铺详情中",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editProduct()
    {
        $rules = [
            'id'            => 'required|integer',
            'title'         => 'required',
            'price'         => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'is_recommend'  => 'in:1,2',
        ];
        $messages = [
            'id.required'           => '产品ID不能为空',
            'id.integer'            => '产品ID必须为整数',
            'title.required'        => '请输入产品标题',
            'price.required'        => '请输入产品价格',
            'price.regex'           => '产品价格格式有误，应为整数或两位小数',
            'image_ids.required'    => '请传入产品图片，至少一张',
            'image_ids.regex'       => '产品图片ID格式有误',
            'is_recommend.in'       => '推荐字段取值有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->productsService->editProduct($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->productsService->error];
        }
        return ['code' => 200, 'message' => $this->productsService->message];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/prime/product_list",
     *     tags={"精选生活OA后台"},
     *     summary="获取产品列表",
     *     description="sang" ,
     *     operationId="product_list",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索【标题，描述】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐，1推荐，2不推荐",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
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
    public function productList()
    {
        $rules = [
            'keywords'      => 'string',
            'is_recommend'  => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'is_recommend.in'           => '推荐字段取值有误',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->productsService->productList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->productsService->error];
        }
        return ['code' => 200, 'message' => $this->productsService->message, 'data' => $res];
    }
}