<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Enums\EmailEnum;
use App\Enums\SMSEnum;
use App\Services\Common\EmailService;
use App\Services\Common\SmsService;
use App\Services\Oa\DepartmentService;
use App\Services\Oa\EmployeeService;
use Illuminate\Support\Facades\Auth;

class OaController extends ApiController
{
    protected $employeeService;
    protected $departmentService;

    /**
     * TestApiController constructor.
     * @param EmployeeService $employeeService
     * @param DepartmentService $departmentService
     */
    public function __construct(EmployeeService $employeeService,DepartmentService $departmentService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->departmentService = $departmentService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/login",
     *     tags={"OA"},
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
     *         description="账户【用户名、手机号、邮箱】",
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
    public function login()
    {
        $rules = [
            'account'  => 'required',
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'account.required'  => '请输入账户',
            'password.required' => '请输入密码',
            'password.min'      => '密码最少6位',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->login($this->request['account'],$this->request['password']);
        if ($res == false){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/logout",
     *     tags={"OA"},
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
     *         description="OA TOKEN",
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
        if ($this->employeeService->logout($this->request['token'])){
            return ['code' => 200, 'message' => '退出成功！'];
        }
        return ['code' => 100, 'message' => '退出失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/refresh",
     *     tags={"OA"},
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
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
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
        if ($token = $this->employeeService->refresh($this->request['token'])){
            return ['code' => 200, 'message' => '刷新成功！', 'data' => ['token' => $token]];
        }
        return ['code' => 100, 'message' => '刷新失败！'];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_user_info",
     *     tags={"OA"},
     *     summary="获取用户信息",
     *     description="sang" ,
     *     operationId="get_user_info",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
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
    public function getUserInfo()
    {
        if ($user = $this->employeeService->getUserInfo()){
            return ['code' => 200, 'message' => '用户信息获取成功！', 'data' => ['user' => $user]];
        }
        return ['code' => 100, 'message' => '用户信息获取失败！'];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_personal_info",
     *     tags={"OA"},
     *     summary="编辑个人信息",
     *     description="sang" ,
     *     operationId="edit_personal_info",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="real_name",
     *         in="query",
     *         description="姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="性别，1男，2女",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="avatar_id",
     *         in="query",
     *         description="头像，（图片ID）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birth_date",
     *         in="query",
     *         description="生日，年-月-日，如：2020-01-10",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="编辑个人信息失败",
     *     ),
     * )
     *
     */
    public function editPersonalInfo()
    {
        $rules = [
            'real_name'     => 'required|max:10',
            'gender'        => 'required|in:1,2',
            'avatar_id'     => 'required|integer',
            'birth_date'    => 'date_format:Y-m-d',
        ];
        $messages = [
            'real_name.required'    => '请输入您的姓名！',
            'real_name.max'         => '姓名字数不能超过10个字！',
            'gender.required'       => '请选择您的性别！',
            'gender.in'             => '性别取值有误！',
            'avatar_id.required'    => '请上传您的头像！',
            'avatar_id.in'          => '头像ID必须为整数！',
            'birth_date.date_format'=> '生日格式有误!',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->editPersonalInfo($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->employeeService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->employeeService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_personal_password",
     *     tags={"OA"},
     *     summary="验证码修改密码",
     *     description="sang" ,
     *     operationId="edit_personal_password",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="手机验证码",
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
     *         name="confirm_password",
     *         in="query",
     *         description="确认新密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改密码失败",
     *     ),
     * )
     *
     */
    public function editPersonalPassword(){
        $rules = [
            'code'                  => 'required',
            'password'              => 'required|min:6|max:20',
            'confirm_password'      => 'required|min:6|max:20',
        ];
        $messages = [
            'code.required'             => '请输入手机验证码！',
            'password.required'         => '请填写新密码！',
            'password.min'              => '密码长度不能低于6位！',
            'password.max'              => '密码长度不能超过20位！',
            'confirm_password.required' => '请填写确认新密码！',
            'confirm_password.min'      => '确认密码长度不能低于6位！',
            'confirm_password.max'      => '确认密码长度不能超过20位！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $employee = Auth::guard('oa_api')->user();
        //短信验证
        $smsService = new SmsService();
        $check_sms = $smsService->checkCode($employee->mobile,SMSEnum::CHANGEPASSWORD, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $res = $this->employeeService->editPersonalPassword($this->request,$employee->id);
        if ($res){
            return ['code' => 200, 'message' => $this->employeeService->message];
        }
        return ['code' => 100, 'message' => $this->employeeService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_password",
     *     tags={"OA"},
     *     summary="使用旧密码修改密码",
     *     description="使用旧密码修改个人登录密码，sang" ,
     *     operationId="edit_password",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="old_password",
     *         in="query",
     *         description="旧密码",
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
     *         name="confirm_password",
     *         in="query",
     *         description="确认新密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改密码失败",
     *     ),
     * )
     *
     */
    public function editPassword(){
        $rules = [
            'old_password'          => 'required',
            'password'              => 'required|min:6|max:20',
            'confirm_password'      => 'required|min:6|max:20',
        ];
        $messages = [
            'old_password.required'     => '请输入旧密码！',
            'password.required'         => '请填写新密码！',
            'password.min'              => '密码长度不能低于6位！',
            'password.max'              => '密码长度不能超过20位！',
            'confirm_password.required' => '请填写确认新密码！',
            'confirm_password.min'      => '确认密码长度不能低于6位！',
            'confirm_password.max'      => '确认密码长度不能超过20位！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->editPassword($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->employeeService->message];
        }
        return ['code' => 100, 'message' => $this->employeeService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_bind_mobile",
     *     tags={"OA"},
     *     summary="修改绑定手机号",
     *     description="sang" ,
     *     operationId="edit_bind_mobile",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
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
    public function editBindMobile(){
        $rules = [
            'password'      => 'required',
            'mobile'        => 'required|mobile',
            'code'          => 'required',
        ];
        $messages = [
            'password.required'     => '请输入密码！',
            'mobile.required'       => '请输入手机号！',
            'mobile.mobile'         => '手机号格式有误！',
            'code.required'         => '请输入验证码！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //短信验证
        $smsService = new SmsService();
        $check_sms = $smsService->checkCode($this->request['mobile'],SMSEnum::BINDMOBILE, $this->request['code']);
        if (is_string($check_sms)){
            return ['code' => 100, 'message' => $check_sms];
        }
        $res = $this->employeeService->editBindMobile($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->employeeService->message];
        }
        return ['code' => 100, 'message' => $this->employeeService->error];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_bind_email",
     *     tags={"OA"},
     *     summary="修改绑定邮箱",
     *     description="sang" ,
     *     operationId="edit_bind_email",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA TOKEN",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="邮箱地址",
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
    public function editBindEmail(){
        $rules = [
            'password'      => 'required',
            'email'         => 'required|email',
            'code'          => 'required',
        ];
        $messages = [
            'password.required'     => '请输入密码！',
            'email.required'        => '请输入邮箱地址！',
            'email.email'           => '邮箱地址格式有误！',
            'code.required'         => '请输入验证码！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //邮箱验证
        $emailService = new EmailService();
        $check_res = $emailService->checkCode($this->request['email'],EmailEnum::BIND_EMAIL, $this->request['code']);
        if ($check_res == false){
            return ['code' => 100, 'message' => $emailService->error];
        }
        $res = $this->employeeService->editBindEmail($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->employeeService->message];
        }
        return ['code' => 100, 'message' => $this->employeeService->error];
    }
}