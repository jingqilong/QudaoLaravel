<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Enums\SMSEnum;
use App\Services\Common\SmsService;
use App\Services\Member\MemberService;
use Illuminate\Http\JsonResponse;

class MemberController extends ApiController
{
    protected $memberService;
    protected $smsService;

    /**
     * TestApiController constructor.
     * @param MemberService $memberService
     * @param SmsService $smsService
     */
    public function __construct(MemberService $memberService,SmsService $smsService)
    {
        parent::__construct();
        $this->memberService = $memberService;
        $this->smsService = $smsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/login",
     *     tags={"会员"},
     *     summary="登录",
     *     description="sang" ,
     *     operationId="login",
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
     *         name="account",
     *         in="query",
     *         description="账户【会员卡号、手机号、邮箱】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="登录失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function login()
    {
        $rules = [
            'account'   => 'required',
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'account.required' => '请输入账户',
            'password.required' => '请输入密码',
            'password.min' => '密码最少6位',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->login($this->request['account'],$this->request['password']);
        if (is_string($res)){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '登录成功！', 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/logout",
     *     tags={"会员"},
     *     summary="退出登录",
     *     description="sang" ,
     *     operationId="logout",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="退出登录失败",
     *     ),
     * )
     *
     */
    /**
     * Log the user out (Invalidate the token).
     *
     * @return array
     */
    public function logout()
    {
        if ($this->memberService->logout($this->request['token'])){
            return ['code' => 200, 'message' => '退出成功！'];
        }
        return ['code' => 100, 'message' => '退出失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/refresh",
     *     tags={"会员"},
     *     summary="刷新TOKEN",
     *     description="sang" ,
     *     operationId="refresh",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="token刷新失败",
     *     ),
     * )
     *
     */
    /**
     * Refresh a token.
     *
     * @return mixed
     */
    public function refresh()
    {
        if ($token = $this->memberService->refresh($this->request['token'])){
            return ['code' => 200, 'message' => '刷新成功！', 'data' => ['token' => $token]];
        }
        return ['code' => 100, 'message' => '刷新失败！'];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_user_list",
     *     tags={"会员"},
     *     summary="获取用户列表",
     *     operationId="get_user_list",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【会员卡号，成员中文名，成员英文名，成员类别，成员手机号】",
     *         required=false,
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
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    /**
     * Get user info.
     * @return array
     */
    public function getUserList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->memberService->getUserList($this->request);
        
        return ['code' => 200,'message' => $this->memberService->message,'data' => $list];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/member/get_user_info",
     *     tags={"会员"},
     *     summary="获取用户信息",
     *     operationId="get_user_info",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    /**
     * Get user info.
     * @return array
     */
    public function getUserInfo()
    {
        if ($user = $this->memberService->getUserInfo()){
            return ['code' => 200, 'message' => '用户信息获取成功！', 'data' => ['user' => $user]];
        }
        return ['code' => 100, 'message' => '用户信息获取失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/sms_login",
     *     tags={"会员"},
     *     summary="短信验证登录",
     *     description="sang" ,
     *     operationId="sms_login",
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
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="验证码",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="登录失败",
     *     ),
     * )
     *
     */
    public function smsLogin(){
        $rules = [
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'      => 'required|min:4|max:4',
        ];
        $messages = [
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
            'code.required'     => '请输入验证码',
            'code.min'          => '验证码不能少于4位',
            'code.max'          => '验证码不能多于4位',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::MEMBERLOGIN, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $res = $this->memberService->mobileLogin($this->request['mobile']);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message'], 'data' => $res['data']];
    }


/*
----------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------
---------------------------------      暂不开发(2019-09-23)         ---------------------------------
----------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------
*/
    /**
     * @return array
     * @persy 暂不开发
     */
    public function updateUserInfo()
    {
        $rules = [
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
            'm_phone'    => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'      => 'required|min:4|max:4',
        ];
        $messages = [
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
            'code.required'     => '请输入验证码',
            'code.min'          => '验证码不能少于4位',
            'code.max'          => '验证码不能多于4位',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
    }
/**************************************** ****************************************/


    /**
     * @OA\Post(
     *     path="/api/v1/member/update_user_password",
     *     tags={"会员"},
     *     summary="会员修改密码",
     *     operationId="update_user_password",
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
     *         name="m_id",
     *         in="query",
     *         description="用户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="m_phone",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="m_password",
     *         in="query",
     *         description="原始密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="m_repwd",
     *         in="query",
     *         description="新密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function updateUserPassword()
    {
        $rules = [
            //'m_password'    => 'required',
            'm_repwd'       => 'required|min:6|max:30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',
            'm_phone'       => 'required|regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            //'m_password.required'   => '请输入原始密码',
            'm_repwd.required'      => '请输入新密码',
            'm_repwd.regex'         => '新密码格式不正确',
            'm_repwd.min'           => '新密码最少6位数',
            'm_repwd.max'           => '新密码最多30位数',
            'mobile.required'       => '请输入手机号',
            'mobile.regex'          => '手机号格式有误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->changePassword($this->request);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/member/sms_update_user_password",
     *     tags={"会员"},
     *     summary="会员短信修改密码",
     *     operationId="sms_update_user_password",
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
     *         name="m_phone",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="短信验证码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="m_password",
     *         in="query",
     *         description="新密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="m_repwd",
     *         in="query",
     *         description="确认密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function forgetPassword()
    {
        $rules = [
            'm_password'    => 'required|min:6|max:30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',
            'm_repwd'       => 'required|same:m_password',
            'm_phone'       => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'          => 'required|min:4|max:4',
        ];
        $messages = [
            'm_password.required' => '请输入新密码',
            'm_password.regex'    => '新密码格式不正确',
            'm_password.min'      => '新密码最少6位数',
            'm_password.max'      => '新密码最多30位数',
            'm_repwd.required'    => '请输入确认密码',
            'm_repwd.same'        => '两次密码不一致',
            'm_phone.required'     => '请输入手机号',
            'm_phone.regex'        => '手机号格式有误',
            'code.required'       => '请输入验证码',
            'code.min'            => '验证码不能少于4位',
            'code.max'            => '验证码不能多于4位',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['m_phone'],SMSEnum::CHANGEPASSWORD, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $res = $this->memberService->smsChangePassword($this->request);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_relation_list",
     *     tags={"会员"},
     *     summary="获取用户推荐关系",
     *     description="sang" ,
     *     operationId="get_relation_list",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="查看类型,1、只查看直接推荐和间接推荐，2、查看详细推荐关系",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户推荐关系获取失败",
     *     ),
     * )
     *
     */
    public function getRelationList(){
        $rules = [
            'type'       => 'required|in:1,2',
        ];
        $messages = [
            'type.required'         => '请输入查看类型',
            'type.in'               => '查看类型不存在'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->getRelationList($this->request['type']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => $res];
    }
}