<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\CollectService;
use App\Services\Activity\PrizeService;
use App\Services\Event\ActivityService;

class UserActivityController extends ApiController
{
    public $activityPrizeService;
    public $activityCollectService;
    public $activityService;

    /**
     * UserActivityController constructor.
     * @param PrizeService $activityPrizeService
     * @param CollectService $collectService
     * @param ActivityService $activityService
     */
    public function __construct(PrizeService $activityPrizeService,CollectService $collectService,ActivityService $activityService)
    {
        parent::__construct();
        $this->activityPrizeService     = $activityPrizeService;
        $this->activityCollectService   = $collectService;
        $this->activityService          = $activityService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_raffle",
     *     tags={"精选活动"},
     *     summary="会员活动抽奖",
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
     * @OA\Post(
     *     path="/api/v1/activity/get_home_list",
     *     tags={"精选活动"},
     *     summary="获取活动首页列表",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
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
     *         name="theme_id",
     *         in="query",
     *         description="活动主题ID，目前有：1、酒会，2、论坛，3、沙龙...",
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
            'is_recommend'  => 'in:0,1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'theme_id.integer'      => '活动主题ID必须为整数',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
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
}