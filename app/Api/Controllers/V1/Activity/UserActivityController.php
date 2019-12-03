<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\CollectService;
use App\Services\Activity\CommentsService;
use App\Services\Activity\DetailService;
use App\Services\Activity\PrizeService;
use App\Services\Activity\RegisterService;

class UserActivityController extends ApiController
{
    public $activityPrizeService;
    public $activityCollectService;
    public $activityService;
    public $registerService;

    /**
     * UserActivityController constructor.
     * @param PrizeService $activityPrizeService
     * @param CollectService $collectService
     * @param DetailService $activityService
     * @param RegisterService $registerService
     */
    public function __construct(PrizeService $activityPrizeService,
                                CollectService $collectService,
                                DetailService $activityService,
                                RegisterService $registerService)
    {
        parent::__construct();
        $this->activityPrizeService     = $activityPrizeService;
        $this->activityCollectService   = $collectService;
        $this->activityService          = $activityService;
        $this->registerService          = $registerService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_raffle",
     *     tags={"精选活动"},
     *     summary="会员活动抽奖",
     *     description="sang" ,
     *     operationId="activity_raffle",
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
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function activityRaffle(){
        $rules = [
            'activity_id'    => 'required|integer',
        ];
        $messages = [
            'activity_id.required'   => '活动ID不能为空',
            'activity_id.integer'    => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->raffle($this->request['activity_id']);
        if ($res){
            return ['code' => 200, 'message' => $this->activityPrizeService->message, 'data' => $res];
        }
        return ['code' => 100, 'message' => $this->activityPrizeService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/is_collect_activity",
     *     tags={"精选活动"},
     *     summary="收藏或取消收藏活动",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="is_collect_activity",
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
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function collectActivity(){
        $rules = [
            'activity_id'    => 'required|integer',
        ];
        $messages = [
            'activity_id.required'   => '活动ID不能为空',
            'activity_id.integer'    => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityCollectService->is_collect($this->request['activity_id']);
        if ($res){
            return ['code' => 200, 'message' => $this->activityCollectService->message];
        }
        return ['code' => 100, 'message' => $this->activityCollectService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/collect_list",
     *     tags={"精选活动"},
     *     summary="获取活动收藏列表",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="collect_list",
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
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="活动状态，1全部，2未开始，3进行中，4已结束",
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
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function collectList(){
        $rules = [
            'type'          => 'required|in:1,2,3,4',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'type.integer'          => '活动类别不能为空',
            'type.in'               => '活动类别不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityCollectService->collectList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityCollectService->error];
        }
        return ['code' => 200, 'message' => $this->activityCollectService->message, 'data' => $res];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/activity/get_home_list",
     *     tags={"精选活动"},
     *     summary="获取活动首页列表",
     *     description="sang" ,
     *     operationId="get_home_list",
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
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【活动名称、活动地点、活动价格】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="活动主题ID，目前有：1、酒会，2、论坛，3、沙龙...【非必填参数为空，表示全部活动】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="收费，1免费，2收费",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1，未开始，2进行中，3已结束",
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
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function getHomeList(){
        $rules = [
            'theme_id'      => 'integer',
            'is_recommend'  => 'in:1',
            'price'         => 'in:1,2',
            'status'        => 'in:1,2,1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'theme_id.integer'      => '活动主题ID必须为整数',
            'is_recommend.in'       => '是否推荐取值应为1',
            'price.in'              => '费用类型不存在',
            'status.in'             => '状态不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->getHomeList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityService->error];
        }
        return ['code' => 200, 'message' => $this->activityService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_activity_detail",
     *     tags={"精选活动"},
     *     summary="获取活动详情",
     *     description="sang" ,
     *     operationId="get_activity_detail",
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
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
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
    public function activityDetail(){
        $rules = [
            'activity_id'   => 'required|integer',
        ];
        $messages = [
            'activity_id.required'  => '活动ID不能为空！',
            'activity_id.integer'   => '活动ID必须为整数！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->getActivityDetail($this->request['activity_id']);
        if ($res){
            return ['code' => 200, 'message' => $this->activityService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->activityService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_my_activity_list",
     *     tags={"精选活动"},
     *     summary="获取我的活动列表",
     *     description="sang" ,
     *     operationId="get_my_activity_list",
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="活动状态，不传获取全部，1、未开始，2、进行中，3、已结束",
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
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function getMyActivityList(){
        $rules = [
            'status'        => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'status.integer'        => '活动状态必须为整数',
            'is_recommend.in'       => '是否推荐取值应为1',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getMyActivityList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message, 'data' => $res];
    }
}