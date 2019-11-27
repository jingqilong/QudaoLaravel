<?php
/**
 * Created By PhpStorm
 * User: jql
 * Date: 2019/11/12
 * Time: 10:53
 */

namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\CartService;

class ShopCarController extends ApiController
{
    public $CartService;

    /**
     * ShopCarController constructor.
     * @param $CartService
     */
    public function __construct(CartService $CartService)
    {
        parent::__construct();
        $this->CartService = $CartService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/add_shop_car",
     *     tags={"商城"},
     *     summary="用户添加商品至购物车",
     *     description="jing" ,
     *     operationId="add_shop_car",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         description="商品id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="spec_relate_id",
     *         in="query",
     *         description="商品规格关联ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="数量",
     *         required=false,
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
    public function addShopCar()
    {
        $rules = [
            'goods_id'            => 'required|integer',
            'spec_relate_id'      => 'required|integer',
            'number'              => 'required|integer',
        ];
        $messages = [
            'goods_id.required'             => '商品id不能为空',
            'goods_id.integer'              => '商品id不是整数',
            'spec_relate_id.required'       => '商品规格关联ID不能为空',
            'spec_relate_id.integer'        => '商品规格关联ID不是整数',
            'number.required'               => '数量不能为空',
            'number.integer'                => '数量不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->CartService->addShopCar($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->CartService->message];
        }
        return ['code' => 100, 'message' => $this->CartService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/shop/del_shop_car",
     *     tags={"商城"},
     *     summary="用户删除购物车商品",
     *     description="jing" ,
     *     operationId="del_shop_car",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="购物车 商品id【 1,2,3】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function delShopCar()
    {
        $rules = [
            'id'            => 'required|string',
        ];
        $messages = [
            'id.required'             => '商品id不能为空',
            'id.string'               => '商品id不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->CartService->delShopCar($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->CartService->message];
        }
        return ['code' => 100, 'message' => $this->CartService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/change_car_num",
     *     tags={"商城"},
     *     summary="用户编辑购物车商品数量",
     *     description="jing" ,
     *     operationId="change_car_num",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="购物车 id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="change",
     *         in="query",
     *         description="变量+ -",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function changeCarNum()
    {
        $rules = [
            'id'                        => 'required|integer',
            'change'                    => 'required|in:+,-',
        ];
        $messages = [
            'id.required'               => '购物车id不能为空',
            'id.integer'                => '购物车id不是整数',
            'change.required'           => '变量不能为空',
            'change.integer'            => '变量取值不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->CartService->changeCarNum($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->CartService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->CartService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/shop_car_list",
     *     tags={"商城"},
     *     summary="用户获取购物车商品列表",
     *     description="jing" ,
     *     operationId="shop_car_list",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function shopCarList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'            => '页码不是整数',
            'page_num.integer'        => '每页显示条数不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->CartService->shopCarList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->CartService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->CartService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/list_shop_car",
     *     tags={"商城后台"},
     *     summary="OA获取购物车列表",
     *     description="jing" ,
     *     operationId="list_shop_car",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索【姓名  手机号】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function listShopCar()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'            => '页码不是整数',
            'page_num.integer'        => '每页显示条数不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->CartService->listShopCar($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->CartService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->CartService->error];
    }

}