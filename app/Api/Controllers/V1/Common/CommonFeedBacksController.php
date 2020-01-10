<?php


namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Services\Common\FeedBacksService;

class CommonFeedBacksController extends ApiController
{
    public $FeedBacksService;

    /**
     * CommonFeedBacksController constructor.
     * @param $FeedBacksService
     */
    public function __construct(FeedBacksService $FeedBacksService)
    {
        parent::__construct();
        $this->FeedBacksService = $FeedBacksService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/add_feedBack",
     *     tags={"公共"},
     *     summary="添加反馈",
     *     description="jing" ,
     *     operationId="add_feedBack",
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
     *         name="content",
     *         in="query",
     *         description="反馈内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function addFeedBack(){
        $rules = [
            'content'   => 'required',
            'mobile'    => 'regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            'content.required'  => '请输入反馈内容',
            'mobile.regex'      => '手机号格式有误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->FeedBacksService->addFeedBack($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->FeedBacksService->error];
        }
        return ['code' => 200, 'message' => $this->FeedBacksService->message];
    }




    /**
     * @OA\Get(
     *     path="/api/v1/common/feed_back_list",
     *     tags={"公共"},
     *     summary="oa 反馈列表",
     *     description="jing" ,
     *     operationId="feed_back_list",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字【手机号，姓名，反馈内容】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="分页页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="分页数量",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function feedBackList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '页码数量必须为整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->FeedBacksService->feedBackList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->FeedBacksService->error];
        }
        return ['code' => 200, 'message' => $this->FeedBacksService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/add_call_back_feed_back",
     *     tags={"公共"},
     *     summary="oa 回复反馈",
     *     description="jing" ,
     *     operationId="add_call_back_feed_back",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="replay_id",
     *         in="query",
     *         description="replay_id【id】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="feedback_id",
     *         in="query",
     *         description="反馈列表id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="反馈内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function addCallBackFeedBack(){
        $rules = [
            'replay_id'   => 'required|integer',
            'feedback_id' => 'required|integer',
            'content'     => 'required',
        ];
        $messages = [
            'replay_id.required'    => '反馈ID不能为空',
            'replay_id.integer'     => '反馈ID必须为整数',
            'feedback_id.required'  => '反馈ID不能为空',
            'feedback_id.integer'   => '反馈ID必须为整数',
            'content.required'      => '页码数量必须为整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->FeedBacksService->addCallBackFeedBack($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->FeedBacksService->error];
        }
        return ['code' => 200, 'message' => $this->FeedBacksService->message];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/common/get_call_back_feed_back",
     *     tags={"公共"},
     *     summary="获取OA反馈的回复详情",
     *     description="jing" ,
     *     operationId="get_call_back_feed_back",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="feedback_id",
     *         in="query",
     *         description="反馈列表id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function getCallBackFeedBack(){
        $rules = [
            'feedback_id' => 'required|integer',
        ];
        $messages = [
            'feedback_id.required'  => '反馈ID不能为空',
            'feedback_id.integer'   => '反馈ID必须为整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->FeedBacksService->getCallBackFeedBack($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->FeedBacksService->error];
        }
        return ['code' => 200, 'message' => $this->FeedBacksService->message,'data' => $res];
    }
}