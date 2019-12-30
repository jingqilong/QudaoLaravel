<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\ActivityService;

class ActivityController extends ApiController
{
    public $activityService;

    /**
     * ActivityController constructor.
     * @param $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        parent::__construct();
        $this->activityService = $activityService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/add_activity_goods",
     *     tags={"商城后台"},
     *     summary="添加活动商品",
     *     description="sang" ,
     *     operationId="add_activity_goods",
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
     *         description="OA_token",
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
     *         name="type",
     *         in="query",
     *         description="活动类别，1积分兑换、2好物推荐，3首页展示",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1禁用，2开启",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="show_image",
     *         in="query",
     *         description="展示图片id，用于首页展示",
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
    public function addActivityGoods(){
        $rules = [
            'goods_id'      => 'required|integer',
            'type'          => 'required|in:1,2,3',
            'status'        => 'required|in:1,2',
            'show_image'    => 'integer',
        ];
        $messages = [
            'goods_id.required'     => '商品ID不能为空',
            'goods_id.integer'      => '商品ID必须为整数',
            'type.required'         => '活动类型不能为空',
            'type.in'               => '不存在该活动类型',
            'status.required'       => '展示状态不能为空',
            'status.in'             => '展示状态取值不存在',
            'show_image.integer'    => '展示图片ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->addActivityGoods($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/shop/delete_activity_goods",
     *     tags={"商城后台"},
     *     summary="删除活动商品",
     *     description="sang" ,
     *     operationId="delete_activity_goods",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="商品活动记录ID",
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
    public function deleteActivityGoods(){
        $rules = [
            'activity_id'       => 'required|integer',
        ];
        $messages = [
            'activity_id.required'  => '商品活动记录ID不能为空',
            'activity_id.integer'   => '商品活动记录ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->deleteActivityGoods($this->request['activity_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/shop/edit_activity_goods",
     *     tags={"商城后台"},
     *     summary="修改活动商品",
     *     description="sang" ,
     *     operationId="edit_activity_goods",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="商品活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1禁用，2开启",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="show_image",
     *         in="query",
     *         description="展示图片id，用于首页展示",
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
    public function editActivityGoods(){
        $rules = [
            'activity_id'       => 'required|integer',
            'status'            => 'required|in:1,2',
            'show_image'        => 'integer',
        ];
        $messages = [
            'activity_id.required'  => '商品活动ID不能为空',
            'activity_id.integer'   => '商品活动ID必须为整数',
            'status.required'       => '展示状态不能为空',
            'status.in'             => '展示状态取值不存在',
            'show_image.integer'    => '展示图片ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->editActivityGoods($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_activity_goods_list",
     *     tags={"商城后台"},
     *     summary="获取活动商品列表",
     *     description="sang" ,
     *     operationId="get_activity_goods_list",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【商品名称】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="活动类别，1积分兑换、2好物推荐，3首页展示",
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
    public function getActivityGoodsList(){
        $rules = [
            'type'          => 'in:1,2,3',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'type.in'               => '不存在该活动类型',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->getActivityGoodsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message, 'data' => $res];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/shop/set_activity_goods_status",
     *     tags={"商城后台"},
     *     summary="设置活动商品状态",
     *     description="sang" ,
     *     operationId="set_activity_goods_status",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="商品活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1禁用，2开启",
     *         required=true,
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
    public function setActivityGoodsStatus(){
        $rules = [
            'activity_id'   => 'required|integer',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'activity_id.required'  => '商品活动ID不能为空',
            'activity_id.integer'   => '商品活动ID必须为整数',
            'status.required'       => '状态不能为空',
            'status.in'             => '状态取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->setActivityGoodsStatus($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message];
    }
}