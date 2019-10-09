<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\DepartmentService;
use App\Services\Oa\EmployeeService;
use Illuminate\Http\JsonResponse;

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
     * @OA\Get(
     *     path="/api/v1/oa/get_depart",
     *     tags={"OA"},
     *     summary="获取部门",
     *     operationId="get_depart",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取部门失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function getDepart()
    {
        $depart = $this->departmentService->getDepart();
        if ($depart['code'] !== 0){
            return ['code' => 200, 'data' => $depart];
        }
        return ['code' => 100, 'message' => $depart['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_depart",
     *     tags={"OA"},
     *     summary="添加部门信息",
     *     operationId="add_depart",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="部门名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="上级部门id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加数据失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function addDepart()
    {
        $rules = [
            'name'          => 'required',
            'parent_id'     => 'required|Integer',
        ];
        $messages = [
            'name.required'         => '请输入部门名称',
            'parent_id.required'    => '请输入上级部门ID',
            'parent_id.number'      => '请正确输入类型',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentService->addDepart($this->request);
        if ($res['code'] == 1){
            return ['code' => 200,'message' => '添加成功'];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/update_depart",
     *     tags={"OA"},
     *     summary="更改部门信息",
     *     operationId="update_depart",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="数据库部门id号(前端传值)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="部门名称（修改名称）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="上级部门id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="path",
     *         in="query",
     *         description="部门路径",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="部门层级",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改数据失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function updateDepart()
    {
        $rules = [
            'name'          => 'required',
            'parent_id'     => 'required|Integer',
        ];
        $messages = [
            'name.required'         => '请输入部门名称',
            'parent_id.required'    => '请输入上级部门ID',
            'parent_id.number'      => '请正确输入类型',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentService->updateDepart($this->request);
        if ($res['code'] == 1){
            return ['code' => 200,'message' => '修改成功'];
        }
        return ['code' => 100,'message' => $res['message']];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/del_depart",
     *     tags={"OA"},
     *     summary="删除部门信息",
     *     operationId="del_depart",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="数据库部门id号(前端传值)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除数据失败",
     *     ),
     * )
     *
     */
    /**
     * @return array
     */
    public function delDepart(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '部门不正确',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id']; //根据主键ID查找  强制转换字符串类型
        $res = $this->departmentService->delDepart($id);
        if ($res['code'] == 1){
            return ['code' => 200,'message' => '删除成功'];
        }
        return ['code' => 100,'message' => $res['message']];
    }
}