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
        if (200 !== $this->memberService->code){
            return ['code' => $this->memberService->code, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => $res];
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
            'referral_code'     => 'string',
        ];
        $messages = [
            'mobile.required'           => '请输入手机号',
            'mobile.regex'              => '手机号格式有误',
            'code.required'             => '请输入验证码',
            'code.min'                  => '验证码不能少于4位',
            'code.max'                  => '验证码不能多于4位',
            'referral_code.string'      => '邀请码格式错误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::REGISTER, $this->request['code']);
        if (is_string($check_sms)){
//            return ['code' => 100, 'message' => $check_sms];
        }
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
            return ['code' => 200, 'message' => $this->memberService->message, 'data' => ['token' => $token]];
        }
        return ['code' => $this->memberService->code, 'message' => $this->memberService->error];
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
     *         description="用户 TOKEN",
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
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【成员中文名，，成员类别，成员手机号】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="排序方式[1 加入时间从远到近 2 加入时间从近到远 3推荐排序 默认1]",
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
            'sort'          => 'in:1,2,3',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'   => '关键字类型不正确',
            'sort.in'           => '排序方式不存在',
            'page.integer'      => '页码不是整数',
            'page_num.integer'  => '每页显示条数不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res  = $this->memberService->getMemberList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->memberService->error];
        }
        return ['code' => 200,'message' => $this->memberService->message,'data' => $res];
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
            'id.required'  => '会员id不能为空',
            'id.integer'   => '会员id不是整数',
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
     *     deprecated=true,
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
     *     path="/api/v1/member/get_member_by_user",
     *     tags={"会员"},
     *     summary="获取成员自己的信息",
     *     operationId="get_member_by_user",
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
    public function getMemberInfoByUser()
    {
        if ($user = $this->memberService->getMemberInfoByUser()){
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
        if ($res === false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => $res];
    }


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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="原始密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="repwd",
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
            'password'    => 'required|min:6|max:30',
            'repwd'       => 'required|min:6|max:30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',
        ];
        $messages = [
            'password.required'   => '请输入原始密码',
            'password.min'        => '密码最少6位数',
            'password.max'        => '密码最多30位数',
            'repwd.required'      => '请输入新密码',
            'repwd.regex'         => '新密码格式不正确',
            'repwd.min'           => '新密码最少6位数',
            'repwd.max'           => '新密码最多30位数',
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
     *         description="短信验证码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="新密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="repwd",
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
            'password'    => 'required|min:6|max:30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',
            'repwd'       => 'required|same:password',
            'mobile'      => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'        => 'required|min:4|max:4',
        ];
        $messages = [
            'password.required' => '请输入新密码',
            'password.regex'    => '新密码格式不正确',
            'password.min'      => '新密码最少6位数',
            'password.max'      => '新密码最多30位数',
            'repwd.required'    => '请输入确认密码',
            'repwd.same'        => '两次密码不一致',
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
        $check_sms = $this->smsService->checkCode($this->request['mobile'],SMSEnum::CHANGEPASSWORD, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $res = $this->memberService->smsChangePassword($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message];
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
     *     @OA\Parameter(name="token",in="query",description="会员 TOKEN",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="ch_name",in="query",description="姓名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="sex",in="query",description="性别 1先生 2女士",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="birthday",in="query",description="生日",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="id_card",in="query",description="身份证",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="address",in="query",description="所在地区",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="referrername",in="query",description="推荐人姓名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="publicity",in="query",description="微信推广[0 不需要 1需要 ]",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="services",in="query",description="其他服务[0 不需要 1需要 ]",required=true,@OA\Schema(type="integer",)),
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
            'sex'                      => 'required|integer',
            'ch_name'                  => 'required|string',
            'email'                    => 'email',
            'birthday'                 => 'required',
            'address'                  => 'required',
            'publicity'                => 'required|in:0,1',
            'services'                 => 'required|in:0,1',
        ];
        $messages = [
            'sex.required'             => '请填写性别',
            'sex.integer'              => '请正确填写性别',
            'ch_name.string'           => '请正确填写姓名',
            'ch_name.required'         => '请填写中文姓名',
            'email.email'              => '邮箱格式不正确',
            'birthday.required'        => '生日未填写',
            'publicity.required'       => '微信推广服务不能为空',
            'publicity.in'             => '微信推广类型不存在',
            'services.required'        => '其他服务不能为空',
            'services.in'              => '其他服务类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = $this->memberService->perfectMemberInfo($this->request);
        if (!$member){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message, 'data' => $member];
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
     *     @OA\Parameter(name="mobile",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="sex",in="query",description="性别 1先生 2女士",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="birthday",in="query",description="生日【格式 2019-11-18】",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="employer",in="query",description="单位",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="industry",in="query",description="从事行业",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="address",in="query",description="地址",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="title",in="query",description="社会职务[泰基企业 董事长,老凤祥 董事长]",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="profile",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),
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
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'sex'               => 'required|in:1,2',
            'email'             => 'email',
            'birthday'          => 'required',
            'address'           => 'required',
        ];
        $messages = [
            'mobile.required'   => '请填写手机号码',
            'mobile.regex'      => '手机号码不正确',
            'birthday.required' => '请填写生日日期',
            'sex.required'      => '请填写性别',
            'sex.in'            => '请正确填写性别',
            'email.email'       => '邮箱格式不正确',
            'address.required'  => '请填写您的地址',
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

    /**
     * @OA\Get(
     *     path="/api/v1/member/personal_center",
     *     tags={"会员"},
     *     summary="个人中心",
     *     operationId="personal_center",
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
     *         description="会员token",
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
    public function personalCenter(){
        $res = $this->memberService->personalCenter();
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/sign",
     *     tags={"会员"},
     *     summary="每日签到",
     *     operationId="sign",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="签到失败",
     *     ),
     * )
     *
     */
    public function sign(){
        $res = $this->memberService->sign();
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/member/sign_details",
     *     tags={"会员"},
     *     summary="签到页详情",
     *     operationId="sign_details",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function signDetails(){
        $res = $this->memberService->signDetails();
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/add_member_contact",
     *     tags={"会员"},
     *     summary="添加成员联系请求",
     *     operationId="add_member_contact",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="contact_id",
     *         in="query",
     *         description="需求联系人id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="needs_value",
     *         in="query",
     *         description="联系需求说明",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态【默认0 已提交 1已审核 2审核驳回】",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function addMemberContact(){
        $rules = [
            'contact_id'       => 'required|integer',
            'needs_value'      => 'required',
        ];
        $messages = [
            'contact_id.required'    => '需求联系人id不能为空',
            'contact_id.integer'     => '需求联系人id不是整数',
            'needs_value.required'   => '需求内容不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->addMemberContact($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message,'data' => $res];
    }
}