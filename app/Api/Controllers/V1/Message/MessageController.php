<?php


namespace App\Api\Controllers\V1\Message;


use App\Api\Controllers\ApiController;
use App\Enums\MessageEnum;
use App\Services\Message\SendService;
use App\Services\Message\SSEService;
use Illuminate\Support\Facades\Auth;

class MessageController extends ApiController
{
    public $sendService;

    /**
     * MessageController constructor.
     * @param $sendService
     */
    public function __construct(SendService $sendService)
    {
        parent::__construct();
        $this->sendService = $sendService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/member_message_list",
     *     tags={"消息"},
     *     summary="会员消息列表",
     *     operationId="member_message_list",
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
     *         description="会员token",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function memberMessageList(){
        $rules = [
            'page'              => 'integer',
            'page_num'          => 'integer'
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->sendService->memberMessageList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/member_message_details",
     *     tags={"消息"},
     *     summary="会员消息详情",
     *     operationId="member_message_details",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="send_id",
     *         in="query",
     *         description="消息发送ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function memberMessageDetails(){
        $rules = [
            'send_id'       => 'required|integer',
        ];
        $messages = [
            'send_id.required'      => '消息发送ID不能为空',
            'send_id.integer'       => '消息发送ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->sendService->getMessageDetail($member->id,MessageEnum::MEMBER,$this->request['send_id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/merchant_message_list",
     *     tags={"消息"},
     *     summary="商户消息列表",
     *     operationId="merchant_message_list",
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
     *         description="商户token",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function merchantMessageList(){
        $rules = [
            'page'              => 'integer',
            'page_num'          => 'integer'
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->sendService->merchantMessageList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/merchant_message_details",
     *     tags={"消息"},
     *     summary="商户消息详情",
     *     operationId="merchant_message_details",
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
     *         description="商户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="send_id",
     *         in="query",
     *         description="消息发送ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function merchantMessageDetails(){
        $rules = [
            'send_id'       => 'required|integer',
        ];
        $messages = [
            'send_id.required'      => '消息发送ID不能为空',
            'send_id.integer'       => '消息发送ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $prime = Auth::guard('prime_api')->user();
        $res = $this->sendService->getMessageDetail($prime->id,MessageEnum::MERCHANT,$this->request['send_id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/oa_message_list",
     *     tags={"消息"},
     *     summary="OA员工消息列表",
     *     operationId="oa_message_list",
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
    public function oaMessageList(){
        $rules = [
            'page'              => 'integer',
            'page_num'          => 'integer'
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->sendService->oaMessageList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/message/oa_message_details",
     *     tags={"消息"},
     *     summary="OA员工消息详情",
     *     operationId="oa_message_details",
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
     *         name="send_id",
     *         in="query",
     *         description="消息发送ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function oaMessageDetails(){
        $rules = [
            'send_id'       => 'required|integer',
        ];
        $messages = [
            'send_id.required'      => '消息发送ID不能为空',
            'send_id.integer'       => '消息发送ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $oa = Auth::guard('oa_api')->user();
        $res = $this->sendService->getMessageDetail($oa->id,MessageEnum::OAEMPLOYEES,$this->request['send_id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->sendService->error];
        }
        return ['code' => 200, 'message' => $this->sendService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/oa_message_details",
     *     tags={"消息"},
     *     summary="OA员工消息详情",
     *     operationId="oa_message_details",
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="通道",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     * )
     *
     */
    public function pushMessage(){
        $rules = [
            'channel'        => 'required',
        ];
        $messages = [
            'channel.required'   => '通道不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        set_time_limit(0);
        $SSEService = new SSEService();
        return $SSEService->pushMessageUnreadCount($this->request['channel']);
    }
}