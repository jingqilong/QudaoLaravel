<?php


namespace App\Api\Controllers\V1\Oa;

use App\Api\Controllers\ApiController;
use App\Services\Oa\EmployeeService;
use Illuminate\Http\JsonResponse;

class EmployessController extends ApiController
{
    protected $employeeService;
    /**
     * TestApiController constructor.
     * @param EmployeeService $employeeService
     */
    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_employee_list",
     *     tags={"OA"},
     *     summary="获取OA员工列表",
     *     description="" ,
     *     operationId="get_employee_list",
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
    public function getEmployeeList()
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
        $list = $this->employeeService->getEmployeeList($this->request);
        if (!$list){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200,'message' => $this->employeeService->message,'data' => $list];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_employee_info",
     *     tags={"OA"},
     *     summary="获取员工信息",
     *     operationId="get_employee_info",
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
     *     @OA\Parameter(name="username",in="query",description="员工ID",required=true,@OA\Schema(type="integer",)),
     *     @OA\Response(response=100,description="获取员工失败",),
     * )
     *
     */
    /**
     * @return array
     */
    public function getEmployeeInfo()
    {
        $rules = [
            'username'   => 'required|integer',
        ];
        $messages = [
            'username.required' => '员工ID不能为空',
            'username.integer'  => '员工ID必须为整数',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->getEmployeeInfo($this->request['id']);
        if ($res['code'] == 200){
            return ['code' => 200,'data' => $res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_employee",
     *     tags={"OA"},
     *     summary="添加员工信息",
     *     operationId="add_employee",
     *     @OA\Parameter(name="sign",in="query",description="签名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="token",in="query",description="token",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="username",in="query",description="账号(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="password",in="query",description="密码(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="repwd",in="query",description="确认密码(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="real_name",in="query",description="昵称(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="department_id",in="query",description="部门id(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="gender",in="query",description="性别 0:男 1：女(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="mobile",in="query",description="手机(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="note",in="query",description="备注(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="work_title",in="query",description="职务(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="birth_date",in="query",description="生日(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="id_card",in="query",description="身份证号(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="status",in="query",description="状态  0:开启  1:禁用  (前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="role_id",in="query",description="用户角色ID (前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="permisions",in="query",description="用户角色ID(前端传值)",required=false,@OA\Schema(type="string",)),*     @OA\Parameter(name="role_id",in="query",description="员工姓名(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(response=100,description="获取员工失败",),
     * )
     *
     */
    /**
     * @return array
     * @desc  添加员工
     */
    public function addEmployee()
    {
        $rules = [
            'username'      => 'required|min:3|max:30',
            'real_name'     => 'min:3|max:30',
            'password'      => 'required|min:6|max:20',
            'repwd'         => 'required|min:6|max:20',
            'department_id' => 'integer',
            'gender'        => 'integer',
            'mobile'        => 'required|regex:/^1[345678][0-9]{9}$/',
            'email'         => 'required|email',
            'id_card'       => 'regex:/^[1-9]\d{14}(\d{2}[0-9x])?$/',
            'status'        => 'required|integer|between:0,2',
            'role_id'       => 'integer',
        ];
        $messages = [
            'username.required' => '请填写员工姓名',
            'username.min' => '员工姓名最少3位',
            'username.max' => '员工姓名最多30位',
            'real_name.min' => '昵称姓名最少3位',
            'real_name.max' => '昵称姓名最多30位',
            'password.required' => '请填写密码',
            'password.min' => '密码不能小于6位数',
            'password.max' => '密码不能大于20位数',
            'department_id.integer' => '部门错误',
            'gender.integer' => '正确填写性别',
            'mobile.required' => '正确填写手机号',
            'email.required' => '请填写邮箱',
            'email.email'    => '请正确填写邮箱',
            'id_card.required' => '正确填写身份证号',
            'status.required' => '状态值不正确',
            'status.integer' => '状态值不正确',
            'status.between' => '状态值不正确',
            'role_id.integer' => '部门不正确',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $request= $this->request;
        if ($request['password'] !== $request['repwd']){
            return ['code' => 100, 'message' => '密码不一致！'];
        }
        $res = $this->employeeService->add($request);
        if ($res['code'] == 1){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/del_employee",
     *     tags={"OA"},
     *     summary="删除员工信息",
     *     operationId="del_employee",
     *     @OA\Parameter(name="sign",in="query",description="签名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="id",in="query",description="员工id(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(response=100,description="获取员工失败",),
     * )
     *
     */
    public function delEmployee()
    {
        $rules = [
            'id'   => 'required',
        ];
        $messages = [
            'id.required' => '找不到该员工，请重试！',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->del($this->request);
        if ($res['code'] == 1){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/update_employee",
     *     tags={"OA"},
     *     summary="更新员工信息",
     *     operationId="update_employee",
     *     @OA\Parameter(name="sign",in="query",description="签名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="real_name",in="query",description="昵称(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="department_id",in="query",description="部门id(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="gender",in="query",description="性别 0:男 1：女(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="mobile",in="query",description="手机(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="note",in="query",description="备注(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="work_title",in="query",description="职务(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="birth_date",in="query",description="生日(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="id_card",in="query",description="身份证号(前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="status",in="query",description="状态  0:开启  1:禁用  (前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="role_id",in="query",description="用户角色ID (前端传值)",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="permisions",in="query",description="用户角色ID(前端传值)",required=false,@OA\Schema(type="string",)),*     @OA\Parameter(name="role_id",in="query",description="员工姓名(前端传值)",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(response=100,description="获取员工失败",),
     * )
     *
     */
    /**
     * @return array
     */
    public function updateEmployee()
    {
        $rules = [
            'real_name'     => 'min:3|max:30',
            'department_id' => 'integer',
            'gender'        => 'integer',
            'mobile'        => 'required|regex:/^1[345678][0-9]{9}$/',
            'email'         => 'required|email',
            'id_card'       => 'regex:/^[1-9]\d{14}(\d{2}[0-9x])?$/',
            'status'        => 'required|integer|between:0,2',
            'role_id'       => 'integer',
        ];
        $messages = [
            'real_name.min' => '昵称姓名最少3位',
            'real_name.max' => '昵称姓名最多30位',
            'department_id.integer' => '部门错误',
            'gender.integer' => '正确填写性别',
            'mobile.required' => '正确填写手机号',
            'email.required' => '请填写邮箱',
            'email.email'    => '请正确填写邮箱',
            'id_card.required' => '正确填写身份证号',
            'status.required' => '状态值不正确',
            'status.integer' => '状态值不正确',
            'status.between' => '状态值不正确',
            'role_id.integer' => '部门不正确',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->update($this->request);
        if ($res['code'] !== 1){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }
}