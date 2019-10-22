<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\CommentsService;

class CommentController extends ApiController
{

    public $commentService;

    /**
     * RegisterController constructor.
     * @param $commentsService
     */
    public function __construct(CommentsService $commentsService)
    {
        parent::__construct();
        $this->commentService = $commentsService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_comment_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动评论列表",
     *     description="sang" ,
     *     operationId="get_comment_list",
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
    public function getCommentList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commentService->getCommentList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commentService->error];
        }
        return ['code' => 200, 'message' => $this->commentService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/audit_comment",
     *     tags={"精选活动后台"},
     *     summary="审核活动评论",
     *     description="sang" ,
     *     operationId="audit_comment",
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
     *         name="comment_id",
     *         in="query",
     *         description="评论ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit",
     *         in="query",
     *         description="审核结果，1通过，2驳回",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="审核失败",
     *     ),
     * )
     *
     */
    public function auditComment(){
        $rules = [
            'comment_id'   => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'comment_id.required'       => '评论ID不能为空',
            'comment_id.integer'        => '评论ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commentService->auditComment($this->request['comment_id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commentService->error];
        }
        return ['code' => 200, 'message' => $this->commentService->message];
    }
}