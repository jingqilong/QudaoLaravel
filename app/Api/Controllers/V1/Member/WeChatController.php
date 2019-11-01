<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Enums\SMSEnum;
use App\Services\Common\SmsService;
use App\Services\Common\WeChatService;

class WeChatController extends ApiController
{
    protected $weChatService;
    protected $smsService;

    /**
     * WeChatController constructor.
     * @param WeChatService $weChatService
     * @param SmsService $smsService
     */
    public function __construct(WeChatService $weChatService,SmsService $smsService)
    {
        parent::__construct();
        $this->weChatService = $weChatService;
        $this->smsService = $smsService;
    }

    /**
     * 微信小程序微信登录接口
     *
     * @OA\Post(
     *     path="/api/v1/member/mini_login",
     *     tags={"会员"},
     *     summary="微信小程序登录",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="mini_login",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="code",
     *     description="js_code",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Response(response=100, description="操作成功：union_id,微信用户信息:wx_user_info,系统用户信息:sys_user_info,token,202表示需要绑定手机号"),
     * )
     */
    public function miniLogin(){
        $rules = [
            'code'              => 'required',
        ];
        $messages = [
            'code.required'             => '小程序码不能为空！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->weChatService->miniLogin($this->request['code']);
        if($result['code'] == 0) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result['data']];
        }
        return ['code' => 202, 'message' => $this->weChatService->message,'data' => $result['data']];
    }


    /**
     * 微信小程序手机绑定接口
     *
     * @OA\Post(
     *     path="/api/v1/member/mini_bind_mobile",
     *     tags={"会员"},
     *     summary="微信小程序登录绑定手机号",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="mini_bind_mobile",
     *   @OA\Parameter(in="query",name="sign",description="签名",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="code",description="js_code",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="mobile",description="手机号",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="captcha",description="验证码",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="referral_code",description="推荐码",required=false,@OA\Schema(type="string",)),
     *   @OA\Response(response="default", description="操作成功：返回用户手机号:mobile、用户token")
     * )
     */
    public function miniBindMobile(){
        $rules = [
            'code'          => 'required',
            'mobile'        => 'required|regex:/^1[3456789][0-9]{9}$/',
            'captcha'       => 'required|max:4|min:4',
        ];
        $messages = [
            'code.required'             => 'code不能为空！',
            'mobile.required'           => '手机号不能为空！',
            'mobile.regex'              => '手机号格式有误！',
            'captcha.required'          => '验证码不能为空！',
            'captcha.max'               => '验证码长度为4位！',
            'captcha.min'               => '验证码长度为4位！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::BINDMOBILE, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $result = $this->weChatService->miniBindMobile($this->request);
        if($result === false) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result];
    }
    /**
     * 微信公众号微信登录接口
     *
     * @OA\Post(
     *     path="/api/v1/member/official_account_login",
     *     tags={"会员"},
     *     summary="微信公众号登录",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="official_account_login",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="code",
     *     description="js_code",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Response(response=200, description="操作成功：微信用户信息:wx_user_info,系统用户信息:sys_user_info,token,202表示需要绑定手机号"),
     * )
     */
    public function officialAccountLogin(){
        $rules = [
            'code'              => 'required',
        ];
        $messages = [
            'code.required'             => 'code不能为空！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->weChatService->officialAccountLogin($this->request['code']);
        if($result['code'] == 0) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result['data']];
        }
        return ['code' => 202, 'message' => $this->weChatService->message,'data' => $result['data']];
    }


    /**
     * 微信小程序手机绑定接口
     *
     * @OA\Post(
     *     path="/api/v1/member/official_account_bind_mobile",
     *     tags={"会员"},
     *     summary="微信公众号登录绑定手机号",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="official_account_bind_mobile",
     *   @OA\Parameter(in="query",name="sign",description="签名",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="code",description="js_code",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="mobile",description="手机号",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="captcha",description="验证码",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="referral_code",description="推荐码",required=false,@OA\Schema(type="string",)),
     *   @OA\Response(response="default", description="操作成功：返回用户手机号:mobile、用户token")
     * )
     */
    public function officialAccountBindMobile(){
        $rules = [
            'code'          => 'required',
            'mobile'        => 'required|regex:/^1[3456789][0-9]{9}$/',
            'captcha'       => 'required|max:4|min:4',
        ];
        $messages = [
            'code.required'             => 'code不能为空！',
            'mobile.required'           => '手机号不能为空！',
            'mobile.regex'              => '手机号格式有误！',
            'captcha.required'          => '验证码不能为空！',
            'captcha.max'               => '验证码长度为4位！',
            'captcha.min'               => '验证码长度为4位！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::BINDMOBILE, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $result = $this->weChatService->officialAccountBindMobile($this->request);
        if($result === false) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result];
    }


    /**
     * 微信登录接口
     *
     * @OA\Post(
     *     path="/api/v1/member/we_chat_login",
     *     tags={"会员"},
     *     summary="微信登录",
     *     description="sang" ,
     *     operationId="we_chat_login",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="code",
     *     description="js_code",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="system",
     *     description="系统，小程序：mini，公众号：officialAccount",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Response(response=100, description="操作成功：union_id,微信用户信息:wx_user_info,系统用户信息:sys_user_info,token,202表示需要绑定手机号"),
     * )
     */
    public function weChatLogin(){
        $rules = [
            'code'              => 'required',
            'system'            => 'required|in:mini,officialAccount',
        ];
        $messages = [
            'code.required'             => '小程序码不能为空！',
            'system.required'           => '系统不能为空！',
            'system.in'                 => '系统不存在！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        if ($this->request['system'] == 'mini'){
            $result = $this->weChatService->miniLogin($this->request['code']);
        }else{
            $result = $this->weChatService->officialAccountLogin($this->request['code']);
        }
        if($result['code'] == 0) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result['data']];
        }
        return ['code' => 202, 'message' => $this->weChatService->message,'data' => $result['data']];
    }


    /**
     * 微信手机绑定接口
     *
     * @OA\Post(
     *     path="/api/v1/member/we_chat_bind_mobile",
     *     tags={"会员"},
     *     summary="微信登录绑定手机号",
     *     description="sang" ,
     *     operationId="we_chat_bind_mobile",
     *   @OA\Parameter(in="query",name="sign",description="签名",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="code",description="js_code",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="system",description="系统，小程序：mini，公众号：officialAccount",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="mobile",description="手机号",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="captcha",description="验证码",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="referral_code",description="推荐码",required=false,@OA\Schema(type="string",)),
     *   @OA\Response(response="default", description="操作成功：返回用户手机号:mobile、用户token")
     * )
     */
    public function weChatBindMobile(){
        $rules = [
            'code'          => 'required',
            'system'        => 'required|in:mini,officialAccount',
            'mobile'        => 'required|regex:/^1[3456789][0-9]{9}$/',
            'captcha'       => 'required|max:4|min:4',
        ];
        $messages = [
            'code.required'             => 'code不能为空！',
            'system.required'           => '系统不能为空！',
            'system.in'                 => '系统不存在！',
            'mobile.required'           => '手机号不能为空！',
            'mobile.regex'              => '手机号格式有误！',
            'captcha.required'          => '验证码不能为空！',
            'captcha.max'               => '验证码长度为4位！',
            'captcha.min'               => '验证码长度为4位！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::BINDMOBILE, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        if ($this->request['system'] == 'mini'){
            $result = $this->weChatService->miniBindMobile($this->request);
        }else{
            $result = $this->weChatService->officialAccountBindMobile($this->request);
        }
        if($result === false) {
            return ['code' => 100, 'message' => $this->weChatService->error];
        }
        return ['code' => 200, 'message' => $this->weChatService->message,'data' => $result];
    }
}