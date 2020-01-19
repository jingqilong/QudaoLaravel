<?php


namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Services\Common\CommentsService;

class CommentsController extends ApiController
{
    public $commonComments;

    /**
     * CommentsController constructor.
     * @param $commonComments
     */
    public function __construct(CommentsService $commonComments)
    {
        parent::__construct();
        $this->commonComments = $commonComments;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/common_list",
     *     tags={"评论管理"},
     *     summary="用户获取评论列表",
     *     description="jing" ,
     *     operationId="common_list",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="商品的ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
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
     *         description="",
     *     ),
     * )
     *
     */
    public function commonList(){
        $rules = [
            'id'            => 'required|integer',
            'type'          => 'required|in:1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'id.required'           => '评论ID不能为空',
            'id.integer'            => '商城类别不能为空',
            'type.required'         => '评论类别不能为空',
            'type.in'               => '评论类别不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '页码数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->commonList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/common/comments_list",
     *     tags={"评论管理"},
     *     summary="oa 获取评论列表",
     *     description="jing" ,
     *     operationId="comments_list",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别[1商城 （目前只有商城）默认全部]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字【姓名,手机号】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态【0、待审核，1、审核通过，2、审核未通过...】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="是否隐藏【0显示，1隐藏】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
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
     *         description="",
     *     ),
     * )
     *
     */
    public function commentsList(){
        $rules = [
            'status'        => 'in:0,1,2',
            'hidden'        => 'in:0,1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'status.in'        => '评论状态类型不存在',
            'hidden.in'        => '评论是否隐藏类型不存在',
            'page.integer'     => '页码必须为整数',
            'page_num.integer' => '页码数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->commentsList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/common/add_comment",
     *     tags={"评论管理"},
     *     summary="用户添加商品评论",
     *     description="jing" ,
     *     operationId="add_comment",
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
     *         name="order_related_id",
     *         in="query",
     *         description="商品订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="【商品订单ID,商品的ID】比如【1,2】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="评论内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="评论图",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function addComment(){
        $rules = [
            'order_related_id' => 'required|integer',
            'related_id'    => 'required',
            'content'       => 'required',
            'type'          => 'required|in:1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'order_related_id.required' => '商品订单ID不能为空',
            'order_related_id.integer'  => '商品订单ID不是整数',
            'related_id.required' => '商品ID不能为空',
            'content.required'    => '评论内容不能为空',
            'type.required'       => '评论类别不能为空',
            'type.in'             => '评论类别不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->addComment($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message,'comment_id' => $res];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/set_comment_status",
     *     tags={"评论管理"},
     *     summary="设置评论状态",
     *     description="jing" ,
     *     operationId="set_comment_status",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="评论状态【1、审核通过，2、审核未通过...】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function setCommentStatus(){
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'id.required'   => '评论ID不能为空',
            'id.integer'    => '评论ID不是整数',
            'status.in'     => '设置评论状态不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->setCommentStatus($this->request['id'],$this->request['status']);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/set_comment_hidden",
     *     tags={"评论管理"},
     *     summary="设置评论是否显示",
     *     description="jing" ,
     *     operationId="set_comment_hidden",
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
     *         name="id",
     *         in="query",
     *         description="评论ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="评论状态【0、显示，1、隐藏...】",
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
    public function setCommentHidden(){
        $rules = [
            'id'            => 'required|integer',
            'hidden'        => 'required|in:0,1',
        ];
        $messages = [
            'id.required'   => '评论ID不能为空',
            'id.integer'    => '评论ID不是整数',
            'hidden.in'     => '显示状态不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->setCommentHidden($this->request['id'],$this->request['hidden']);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_comment_details",
     *     tags={"评论管理"},
     *     summary="oa获取评论详情",
     *     description="jing" ,
     *     operationId="get_comment_details",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
     *         description="",
     *     ),
     * )
     *
     */
    public function getCommentDetails(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'   => '评论ID不能为空',
            'id.integer'    => '评论id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonComments->getCommentDetails($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->commonComments->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->commonComments->error];
    }

}