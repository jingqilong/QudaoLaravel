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
     *     path="/api/v1/member/mobile_register",
     *     tags={"会员"},
     *     summary="手机号码注册",
     *     description="sang" ,
     *     operationId="mobile_register",
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
     *      @OA\Parameter(
     *         name="referral_code",
     *         in="query",
     *         description="邀请码",
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
    public function mobileRegister(){
        $rules = [
            'mobile'            => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'              => 'required|min:4|max:4',
            'referral_code'   => 'string',
        ];
        $messages = [
            'mobile.required'           => '请输入手机号',
            'mobile.regex'              => '手机号格式有误',
            'code.required'             => '请输入验证码',
            'code.min'                  => '验证码不能少于4位',
            'code.max'                  => '验证码不能多于4位',
            'referral_code.string'    => '邀请码格式错误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        /*$check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::REGISTER, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }*/
        $res = $this->memberService->register($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => $res];
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
     *     path="/api/v1/member/get_member_list",
     *     tags={"会员"},
     *     summary="获取成员列表（模糊搜索）",
     *     operationId="get_member_list",
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
     *         name="asc",
     *         in="query",
     *         description="排序方式[1 最早加入 2 最新加入]",
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
    public function getMemberList()
    {
        $rules = [
            'keywords'      => 'string',
            'page'          => 'integer',
            'page_num'      => 'integer',
            'asc'           => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
            'asc.integer'               => '排序方式不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->memberService->getMemberList($this->request);

        return ['code' => 200,'message' => $this->memberService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_info",
     *     tags={"会员"},
     *     summary="成员查看成员信息",
     *     operationId="get_member_info",
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
     *         description="用户 TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="用户id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getMemberInfo()
    {
        $rules = [
            'id'           => 'required|integer',
        ];
        $messages = [
            'id.required'           => '会员id不能为空',
            'id.integer'            => '查看会员id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->memberService->getMemberInfo($this->request);

        return ['code' => 200,'message' => $this->memberService->message,'data' => $list];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_category_list",
     *     tags={"会员"},
     *     description="jing" ,
     *     summary="根据成员分类查找会员列表",
     *     operationId="get_member_category_list",
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
     *         name="category",
     *         in="query",
     *         description="成员类别[ 1 商政名流 2 企业精英 3 名医专家 4 文艺雅仕]",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="排序方式[1 正序(默认) 2 倒叙]",
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
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getMemberCategoryList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'asc'           => 'in:1,2',
        ];
        $messages = [
            'page.integer'      => '页码不是整数',
            'page_num.integer'  => '每页显示条数不是整数',
            'asc.in'            => '排序方式不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->memberService->getMemberCategoryList($this->request);

        return ['code' => 200,'message' => $this->memberService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_user_info",
     *     tags={"会员"},
     *     summary="获取成员自己的信息",
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
            return ['code' => 200, 'message' => '成员信息获取成功！', 'data' => ['user' => $user]];
        }
        return ['code' => 100, 'message' => '成员信息获取失败！'];
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

    /*public function updateUserInfo()
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
    }*/


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
        if ($res === false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message];
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


    /**
     * @OA\Post(
     *     path="/api/v1/member/perfect_member_info",
     *     tags={"会员"},
     *     summary="手机号码注册完善用户信息",
     *     operationId="perfect_member_info",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="m_phone",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_cname",in="query",description="姓名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_sex",in="query",description="性别 1先生 2女士",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_birthday",in="query",description="生日",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_numcard",in="query",description="身份证",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_address",in="query",description="所在地区",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_referrername",in="query",description="推荐人姓名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_wechattext",in="query",description="微信推广",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_services",in="query",description="其他服务",required=false,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function perfectMemberInfo()
    {
        $rules = [
            'm_phone'                    => 'required|regex:/^1[35678][0-9]{9}$/',
            'm_sex'                      => 'required|integer',
            'm_cname'                    => 'required|string',
            'm_email'                    => 'email',
            'm_birthday'                 => 'required',
            //'m_numcard'                  => 'required|regex:/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/',
            'm_address'                  => 'required',
        ];
        $messages = [
            'm_phone.required'           => '请填写手机号码',
            'm_phone.regex'              => '手机号码不正确',
            'm_sex.required'             => '请填写性别',
            'm_sex.integer'              => '请正确填写性别',
            'm_cname.string'             => '请正确填写姓名',
            'm_cname.required'           => '请填写中文姓名',
            'm_email.email'              => '邮箱格式不正确',
            'm_birthday.required'        => '生日未填写',
            'm_address.required'         => '请填写您的地址',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = $this->memberService->perfectMemberInfo($this->request);
        if (!$member){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => ['memberId' => $member]];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_member_info",
     *     tags={"会员"},
     *     summary="成员编辑个人信息",
     *     operationId="edit_member_info",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="token",in="query",description="用户 token",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_phone",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_sex",in="query",description="性别 1先生 2女士",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_birthday",in="query",description="生日【格式 2019-11-18】",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_email",in="query",description="邮箱",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_workunits",in="query",description="单位",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_industry",in="query",description="从事行业",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_address",in="query",description="地址",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_zipaddress",in="query",description="杂志寄送地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_socialposition",in="query",description="社会职务[泰基企业 董事长,老凤祥 董事长]",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_introduce",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function editMemberInfo()
    {
        $rules = [
            'm_phone'                    => 'required|regex:/^1[35678][0-9]{9}$/',
            'm_sex'                      => 'required|in:1,2',
            'm_email'                    => 'required|email',
            'm_birthday'                 => 'required',
            'm_address'                  => 'required',
        ];
        $messages = [
            'm_phone.required'           => '请填写手机号码',
            'm_birthday.required'        => '请填写生日',
            'm_phone.regex'              => '手机号码不正确',
            'm_sex.required'             => '请填写性别',
            'm_sex.integer'              => '请正确填写性别',
            'm_email.email'              => '邮箱格式不正确',
            'm_email.required'           => '邮箱格式不正确',
            'm_address.required'         => '请填写您的地址',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = $this->memberService->editMemberInfo($this->request);
        if (!$member){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_user_count",
     *     tags={"会员"},
     *     summary="获取成员人数",
     *     operationId="get_user_count",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getUserCount()
    {
        $member = $this->memberService->getUserCount();
        $all_member = $member[0]['value'];
        unset($member[0]);
        if (!$member){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'all_member' => $all_member, 'message' => $this->memberService->message,'data' => $member];
    }
}