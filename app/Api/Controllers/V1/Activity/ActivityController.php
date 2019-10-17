<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Event\ActivityService;

class ActivityController extends ApiController
{
    public $activityService;

    /**
     * ActivityController constructor.
     * @param ActivityService $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        parent::__construct();
        $this->activityService  = $activityService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity",
     *     tags={"精选活动后台"},
     *     summary="添加活动",
     *     operationId="add_activity",
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
     *         description="活动名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="活动地点",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="活动价格（单位：元）",
     *         required=false,
     *         @OA\Schema(
     *             type="float"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="活动主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="活动开始时间（例如：2019-10-01 08:30）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="活动结束时间",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="site_id",
     *         in="query",
     *         description="场地ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="supplies_ids",
     *         in="query",
     *         description="相关用品id串（例如1,2,66,）",
     *         required=false,
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
     *         name="banner_ids",
     *         in="query",
     *         description="轮播图ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="详情图ID串（例如22,56,56）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="firm",
     *         in="query",
     *         description="参会单位（多个单位使用|隔开）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notice",
     *         in="query",
     *         description="参会须知",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="活动详情",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="活动状态（1、开启，2、关闭）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_member",
     *         in="query",
     *         description="是否允许非会员参加（1、不允许，2、允许）",
     *         required=true,
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
    public function addActivity(){
        $rules = [
            'name'          => 'required',
            'address'       => 'required',
            'price'         => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'theme_id'      => 'required|integer',
            'start_time'    => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'end_time'      => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'site_id'       => 'required|integer',
            'supplies_ids'  => 'regex:/^(\d+[,])*$/',
            'is_recommend'  => 'in:0,1',
            'banner_ids'    => 'required|regex:/^(\d+[,])*\d+$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'status'        => 'required|in:1,2',
            'is_member'     => 'required|in:1,2',
        ];
        $messages = [
            'name.required'         => '活动名称不能为空',
            'address.required'      => '活动地点不能为空',
            'price.regex'           => '活动价格格式有误',
            'theme_id.required'     => '活动主题不能为空',
            'theme_id.integer'      => '活动主题必须为整数',
            'start_time.required'   => '活动开始时间不能为空',
            'start_time.regex'      => '活动开始时间格式有误，例如：2019-10-10 12:30',
            'end_time.required'     => '活动结束时间不能为空',
            'end_time.regex'        => '活动结束时间格式有误，例如：2019-10-10 12:30',
            'site_id.required'      => '活动场地不能为空',
            'site_id.integer'       => '活动场地ID必须为整数',
            'supplies_ids.regex'    => '活动用品ID串格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'banner_ids.required'   => '活动banner图不能为空',
            'banner_ids.regex'      => '活动banner图ID串格式有误',
            'image_ids.required'    => '活动详情图不能为空',
            'image_ids.regex'       => '活动详情图ID串格式有误',
            'status.required'       => '活动状态不能为空',
            'status.in'             => '活动状态取值不在范围内',
            'is_member.required'    => '是否允许非会员参加不能为空',
            'is_member.in'          => '是否允许非会员参加取值不在范围内',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->addActivity($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->activityService->message];
        }
        return ['code' => 100, 'message' => $this->activityService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity",
     *     tags={"精选活动后台"},
     *     summary="软删除活动",
     *     operationId="delete_activity",
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
     *         description="活动ID",
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
    public function deleteActivity(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'       => '活动ID不能为空',
            'id.integer'        => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->softDeleteActivity($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->activityService->message];
        }
        return ['code' => 100, 'message' => $this->activityService->error];
    }
}