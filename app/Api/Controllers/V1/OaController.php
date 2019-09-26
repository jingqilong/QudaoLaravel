<?php


namespace App\Api\Controllers\V1;


use App\Api\Controllers\ApiController;
use App\Services\Oa\EmployeeService;
use Illuminate\Http\JsonResponse;

class OaController extends ApiController
{
    protected $employeeService;

    /**
     * TestApiController constructor.
     * @param $employeeService
     */
    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/login",
     *     tags={"OA"},
     *     summary="登录",
     *     operationId="login",
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
        $res = $this->employeeService->login($this->request['account'],$this->request['password']);
        if (is_string($res)){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '登录成功！', 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/logout",
     *     tags={"OA"},
     *     summary="退出登录",
     *     operationId="logout",
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
     *     operationId="refresh",
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
     *     operationId="get_user_info",
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
        if ($user = $this->employeeService->getUserInfo()){
            return ['code' => 200, 'message' => '用户信息获取成功！', 'data' => ['user' => $user]];
        }
        return ['code' => 100, 'message' => '用户信息获取失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/refresh",
     *     tags={"OA"},
     *     summary="刷新TOKEN",
     *     operationId="refresh",
     *     @OA\Parameter(name="token",in="query",description="用户TOKEN",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="username",in="query",description="用户名",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="real_name",in="query",description="真实姓名",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="department_id",in="query",description="部门ID",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="gender",in="query",description="姓别",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="mobile",in="query",description="手机号",required=true,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="email",in="query",description="电子邮件",required=false,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="work_title",in="query",description="职务",required=false,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="birth_date",in="query",description="生日",required=false,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="id_card",in="query",description="身份证号",required=false,@OA\Schema(type="string",), ),
     *     @OA\Parameter(name="note",in="query",description="备注",required=false,@OA\Schema(type="string",), ),
     *     @OA\Response(
     *         response=100,
     *         description="token刷新失败",
     *     ),
     * )
     *
     */
    public function AddEmployee(){
        $rules = [
            'username'   => 'required',
            'real_name' => 'required|string|min:6',
            'department_id'   => 'required',
            'gender' => 'required|string|min:6',
            'mobile'   => 'required',
            'email' => 'email',
            /**
            'work_title'   => 'required',
            'birth_date' => 'required|string|min:6',
            'id_card'   => 'id_card',
            'note' => 'required|string|min:6',\
             */
        ];
        $messages = [
            'username'   => '请输入员工用户名。',
            'real_name' => '请输入员工姓名。',
            'department_id'   => '请选择用户所在部门。',
            'gender' => '请选择员工性别。',
            'mobile'   => '请输入员工手机号。',
            'email' => '电子邮件格式不正确。',
            /**
            'work_title'   => 'required',
            'birth_date' => 'required|string|min:6',
            'id_card'   => 'required',
            'note' => 'required|string|min:6',
             */
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->AddEmployee($this->request);
        if ($res){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '登录成功！', 'data' => $res];
    }

}