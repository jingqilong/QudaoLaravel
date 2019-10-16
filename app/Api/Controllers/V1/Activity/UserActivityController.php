<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\CollectService;
use App\Services\Activity\CommentsService;
use App\Services\Activity\PrizeService;
use App\Services\Event\ActivityService;

class UserActivityController extends ApiController
{
    public $activityPrizeService;
    public $activityCollectService;
    public $activityService;
    public $commentsService;

    /**
     * UserActivityController constructor.
     * @param PrizeService $activityPrizeService
     * @param CollectService $collectService
     * @param ActivityService $activityService
     * @param CommentsService $commentsService
     */
    public function __construct(PrizeService $activityPrizeService,
                                CollectService $collectService,
                                ActivityService $activityService,
                                CommentsService $commentsService)
    {
        parent::__construct();
        $this->activityPrizeService     = $activityPrizeService;
        $this->activityCollectService   = $collectService;
        $this->activityService          = $activityService;
        $this->commentsService          = $commentsService;
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
     * @OA\Post(
     *     path="/api/v1/activity/comment",
     *     tags={"精选活动"},
     *     summary="会员评论活动",
     *     operationId="comment",
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
     *         description="token【会员】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score",
     *         in="query",
     *         description="评分，满分10分",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keyword_ids",
     *         in="query",
     *         description="评论关键字ID串，【例如：22,33,55,】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="评论内容",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="comment_name",
     *         in="query",
     *         description="评论人名称，或微信昵称",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="comment_avatar",
     *         in="query",
     *         description="评论人头像，或微信头像",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
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
     *         description="评论失败！",
     *     ),
     * )
     *
     */
    public function comment(){
        $rules = [
            'score'         => 'required|min:1|max:10',
            'keyword_ids'   => 'required|regex:/^(\d+[,])*$/',
            'comment_avatar'=> 'url',
            'activity_id'   => 'required|integer',
        ];
        $messages = [
            'score.required'        => '评分不能为空',
            'score.min'             => '评分不能低于1分',
            'score.max'             => '评分不能高于10分',
            'keyword_ids.required'  => '评论关键字不能为空',
            'keyword_ids.regex'     => '活动主题ID必须为整数',
            'comment_avatar.url'    => '头像链接格式有误',
            'activity_id.required'  => '活动ID不能为空',
            'activity_id.integer'   => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commentsService->comment($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commentsService->message];
        }
        return ['code' => 100, 'message' => $this->commentsService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_comment",
     *     tags={"精选活动"},
     *     summary="删除活动评论",
     *     operationId="delete_comment",
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
     *         description="token【会员】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="评论ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败！",
     *     ),
     * )
     *
     */
    public function deleteComment(){
        $rules = [
            'id'   => 'required|integer',
        ];
        $messages = [
            'id.required'  => '评论ID不能为空',
            'id.integer'   => '评论ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commentsService->deleteComment($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->commentsService->message];
        }
        return ['code' => 100, 'message' => $this->commentsService->error];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_register",
     *     tags={"精选活动"},
     *     summary="活动报名",
     *     operationId="activity_register",
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
     *         description="token【会员】",
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
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="报名失败！",
     *     ),
     * )
     *
     */
    public function activityRegister(){
        $rules = [
            'activity_id'   => 'required|integer',
            'name'          => 'required',
            'mobile'        => 'required|regex:/^1[3-9]\d{9}$/',
        ];
        $messages = [
            'activity_id.required'  => '活动ID不能为空！',
            'activity_id.integer'   => '活动ID必须为整数！',
            'name.required'         => '姓名不能为空！',
            'mobile.required'       => '手机号不能为空！',
            'mobile.regex'          => '手机号格式有误！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commentsService->register($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commentsService->message];
        }
        return ['code' => 100, 'message' => $this->commentsService->error];
    }
}