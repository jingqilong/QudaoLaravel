<?php


namespace App\Api\Controllers\V1\Message;


use App\Api\Controllers\ApiController;
use App\Services\Message\SendService;

class OaMessageController extends ApiController
{
    public $messageService;

    /**
     * OaMessageController constructor.
     * @param $messageService
     */
    public function __construct(SendService $messageService)
    {
        parent::__construct();
        $this->messageService = $messageService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/get_all_message_list",
     *     tags={"消息后台"},
     *     summary="获取所有消息列表",
     *     operationId="get_all_message_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *         name="user_type",
     *         in="query",
     *         description="用户类别，默认1会员、2商户、3OA员工",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="message_category",
     *         in="query",
     *         description="消息分类ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getAllMessageList(){
        $rules = [
            'user_type'         => 'in:1,2,3',
            'message_category'  => 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer'
        ];
        $messages = [
            'user_type.in'              => '用户类别不存在',
            'message_category.integer'  => '消息分类ID必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->messageService->getAllMessageList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->messageService->error];
        }
        return ['code' => 200, 'message' => $this->messageService->message,'data' => $res];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/message/send_system_notice",
     *     tags={"消息后台"},
     *     summary="发送系统通知",
     *     operationId="send_system_notice",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *         name="user_id",
     *         in="query",
     *         description="用户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="user_type",
     *         in="query",
     *         description="用户类别，默认1会员、2商户、3OA员工",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="消息标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="消息内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="消息相关图片ID串",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="相关链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function sendSystemNotice(){
        $rules = [
            'user_id'           => 'required|integer',
            'user_type'         => 'required|in:1,2,3',
            'title'             => 'required',
            'content'           => 'required',
            'image_ids'         => 'regex:/^(\d+[,])*\d+$/',
            'url'               => 'url',
        ];
        $messages = [
            'user_id.required'          => '用户ID不能为空',
            'user_id.in'                => '用户ID必须为整数',
            'user_type.required'        => '用户类别不能为空',
            'user_type.in'              => '用户类别不存在',
            'title.required'            => '消息标题不能为空',
            'content.required'          => '消息内容不能为空',
            'image_ids.regex'           => '消息相关图片格式有误',
            'url.url'                   => '相关链接必须是一个链接',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->messageService->sendSystem($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->messageService->error];
        }
        return ['code' => 200, 'message' => $this->messageService->message];
    }
}