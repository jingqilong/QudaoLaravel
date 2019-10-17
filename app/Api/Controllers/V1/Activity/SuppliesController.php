<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\SuppliesService;

class SuppliesController extends ApiController
{
    public $suppliesService;

    /**
     * SuppliesController constructor.
     * @param $suppliesService
     */
    public function __construct(SuppliesService $suppliesService)
    {
        parent::__construct();
        $this->suppliesService = $suppliesService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="添加活动用品",
     *     operationId="add_activity_supplies",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="用品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="用品价格（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认0，1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="用品链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="用品详情",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="用品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="用品来源（1、自营，2、第三方）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="使用场景主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameter",
     *         in="query",
     *         description="用品参数，json格式数据（例如：{'品名':'吉祥如意','材料':'红檀木（非洲红酸枝）','适用':'自用、收藏、摆件、送礼','如意':'长31cm 高9.5cm 宽9cm'}）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addActivitySupplies(){
        $rules = [
            'name'          => 'required',
            'price'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'is_recommend'  => 'in:0,1',
            'link'          => 'url',
            'detail'        => 'required',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'source'        => 'required|in:1,2',
            'theme_id'      => 'required|integer',
            'parameter'     => 'json'
        ];
        $messages = [
            'name.required'         => '用品名称不能为空',
            'price.required'        => '用品价格不能为空',
            'price.regex'           => '用品价格格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'link.url'              => '用品链接不是一个url',
            'detail.required'       => '用品详情不能为空',
            'image_ids.required'    => '用品图片不能为空',
            'image_ids.regex'       => '用品图片ID串格式有误',
            'source.required'       => '用品来源不能为空',
            'source.in'             => '用品来源不存在',
            'theme_id.required'     => '主题不能为空',
            'theme_id.integer'      => '主题ID必须为整数',
            'parameter.json'        => '用品参数必须为json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->addSupplies($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="删除活动用品",
     *     operationId="delete_activity_supplies",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="用品ID",
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
    public function deleteActivitySupplies(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'       => '用品ID不能为空',
            'id.integer'        => '用品ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->deleteSupplies($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="修改活动用品",
     *     operationId="edit_activity_supplies",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="用品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="用品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="用品价格（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认0，1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="用品链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="用品详情",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="用品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="用品来源（1、自营，2、第三方）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="使用场景主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameter",
     *         in="query",
     *         description="用品参数，json格式数据（例如：'{'品名':'吉祥如意','材料':'红檀木（非洲红酸枝）','适用':'自用、收藏、摆件、送礼','如意':'长31cm 高9.5cm 宽9cm'}'）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editActivitySupplies(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'price'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'is_recommend'  => 'in:0,1',
            'link'          => 'url',
            'detail'        => 'required',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'source'        => 'required|in:1,2',
            'theme_id'      => 'required|integer',
            'parameter'     => 'json',
        ];
        $messages = [
            'id.required'           => '用品ID不能为空',
            'id.integer'            => '用品ID必须为整数',
            'name.required'         => '用品名称不能为空',
            'price.required'        => '用品价格不能为空',
            'price.regex'           => '用品价格格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'link.url'              => '用品链接不是一个url',
            'detail.required'       => '用品详情不能为空',
            'image_ids.required'    => '用品图片不能为空',
            'image_ids.regex'       => '用品图片ID串格式有误',
            'source.required'       => '用品来源不能为空',
            'source.in'             => '用品来源不存在',
            'theme_id.required'     => '主题不能为空',
            'theme_id.integer'      => '主题ID必须为整数',
            'parameter.json'        => '用品参数必须为json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->editSupplies($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_supplies_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动用品列表",
     *     operationId="activity_supplies_list",
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
     *         description="token",
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
    public function activitySuppliesList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->getSuppliesList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100, 'message' => $this->suppliesService->error];
        }
        return ['code' => 200, 'message' => $this->suppliesService->message, 'data' => $res];
    }
}