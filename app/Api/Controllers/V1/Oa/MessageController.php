<?php


namespace App\Api\Controllers\V1\Oa;

use App\Api\Controllers\ApiController;
use App\Services\Oa\MessageService;

class MessageController extends ApiController
{
    public $messageService;

    /**
     * QiNiuController constructor.
     * @param $messageService
     */
    public function __construct(MessageService $messageService)
    {
        parent::__construct();
        $this->messageService = $messageService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_push_auth",
     *     tags={"OA"},
     *     summary="添加web推送授权信息【添加、更新，两用】",
     *     operationId="add_push_auth",
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
     *         name="endpoint",
     *         in="query",
     *         description="端点",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="public_key",
     *         in="query",
     *         description="公钥",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="auth_token",
     *         in="query",
     *         description="用户令牌【并非上面的token】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content_encoding",
     *         in="query",
     *         description="内容编码",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="上传失败",
     *     ),
     * )
     *
     */
    public function addPushAuth(){
        $rules = [
            'endpoint'          => 'required',
            'public_key'        => 'required',
            'auth_token'        => 'required',
            'content_encoding'  => 'required',
        ];
        $messages = [
            'endpoint.required'         => '端点不能为空',
            'public_key.required'       => '公钥不能为空',
            'auth_token.required'       => '用户令牌不能为空',
            'content_encoding.required' => '内容编码不能为空',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->messageService->addPushAuth($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->messageService->error];
        }
        return ['code' => 200, 'message' => $this->messageService->message, 'data' => $res];
    }

    public function push(){
        $res = $this->messageService->push(
            1,
            '审核通知',
            '您有一位用户等待审核中，请立即前往进行审核！',
            '223',
            '立即前往',
            'https://www.baidu.com');
        return [
            'error'     => $this->messageService->error,
            'message'   => $this->messageService->message,
            'data'      => $res
            ];
    }
}