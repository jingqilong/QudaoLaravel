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
     *     description="sang" ,
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
     *         description="OA_token",
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
     *     description="sang" ,
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
     *         description="OA_token",
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



    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity",
     *     tags={"精选活动后台"},
     *     summary="修改活动",
     *     description="sang" ,
     *     operationId="edit_activity",
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
     *         name="id",
     *         in="query",
     *         description="活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
    public function editActivity(){
        $rules = [
            'id'            => 'required|integer',
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
            'is_recommend'  => 'in:0,1',
            'banner_ids'    => 'required|regex:/^(\d+[,])*\d+$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'status'        => 'required|in:1,2',
            'is_member'     => 'required|in:1,2',
        ];
        $messages = [
            'id.required'           => '活动ID不能为空',
            'id.integer'            => '活动ID必须为整数',
            'name.required'         => '活动名称不能为空',
            'address.required'      => '活动地点不能为空',
            'price.regex'           => '活动价格格式有误',
            'theme_id.required'     => '活动主题不能为空',
            'theme_id.integer'      => '活动主题必须为整数',
            'start_time.required'   => '活动开始时间不能为空',
            'start_time.regex'      => '活动开始时间格式有误，例如：2019-10-10 12:30',
            'end_time.required'     => '活动结束时间不能为空',
            'end_time.regex'        => '活动结束时间格式有误，例如：2019-10-10 12:30',
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
        $res = $this->activityService->editActivity($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->activityService->message];
        }
        return ['code' => 100, 'message' => $this->activityService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_activity_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动列表",
     *     description="sang" ,
     *     operationId="get_activity_list",
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
     *         description="搜索内容【活动名称、活动地点、活动价格、主题、参会单位】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="活动时间范围开始（例如：2019-10-01 08:30）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="活动时间范围结尾（例如：2019-10-02 08:30）",
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
     *         name="status",
     *         in="query",
     *         description="活动状态（1、开启，2、关闭）",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_member",
     *         in="query",
     *         description="是否允许非会员参加（1、不允许，2、允许）",
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
    public function getActivityList(){
        $rules = [
            'start_time'    => [
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'end_time'      => [
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'is_recommend'  => 'in:0,1',
            'status'        => 'in:1,2',
            'is_member'     => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'price.regex'           => '活动价格格式有误，正确格式：29.0或29.99',
            'start_time.regex'      => '活动时间范围开始时间格式有误，正确格式：2019-10-10 12:30',
            'end_time.regex'        => '活动时间范围结尾时间格式有误，正确格式：2019-10-10 12:30',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'status.in'             => '活动状态取值不在范围内',
            'is_member.in'          => '是否允许非会员参加取值不在范围内',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->getActivityList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_detail",
     *     tags={"精选活动后台"},
     *     summary="获取获取详细信息",
     *     description="sang" ,
     *     operationId="activity_detail",
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
    public function activityDetail(){
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
        $res = $this->activityService->activityDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message,'data' => $res];
    }
}