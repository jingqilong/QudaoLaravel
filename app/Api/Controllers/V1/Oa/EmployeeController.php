<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\EmployeeService;

class EmployeeController extends ApiController
{
    protected $employeeService;

    /**
     * TestApiController constructor.
     * @param EmployeeService $employeeService
     */
    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();
        $this->employeeService          = $employeeService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_user",
     *     tags={"OA权限管理"},
     *     summary="添加员工",
     *     description="sang" ,
     *     operationId="add_user",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="用户名",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="real_name",
     *         in="query",
     *         description="名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="head_portrait",
     *         in="query",
     *         description="头像【URL】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
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
     *     @OA\Parameter(
     *         name="confirm_password",
     *         in="query",
     *         description="确认密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="roles",
     *         in="query",
     *         description="角色ID，【EG：1,2,13,】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="permission_ids",
     *         in="query",
     *         description="权限ID，【EG：1,2,13,】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addUser()
    {
        $rules = [
            'username'          => 'required',
            'real_name'         => 'required',
            'email'             => 'required',
            'mobile'            => 'required',
            'head_portrait'     => 'required',
            'password'          => 'required|min:6|max:20',
            'confirm_password'  => 'required|min:6|max:20',
            'roles'             => 'regex:/^(\d+[,])*\d+$/',
            'permission_ids'    => 'regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'username.required'         => '请填写用户名',
            'real_name.required'        => '请填写用户名称',
            'email.required'            => '请填写用户邮箱',
            'mobile.required'           => '请填写用户手机号',
            'head_portrait.required'    => '请上传用户头像',
            'password.required'         => '请填写密码',
            'password.min'              => '密码长度不能低于6位',
            'password.max'              => '密码长度不能超过20位',
            'confirm_password.required' => '请填写确认密码',
            'confirm_password.min'      => '确认密码长度不能低于6位',
            'confirm_password.max'      => '确认密码长度不能超过20位',
            'roles.regex'               => '角色ID格式有误',
            'permission_ids.regex'      => '权限ID格式有误',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        if ($this->request['password'] != $this->request['confirm_password']){
            return ['code' => 100, 'message' => '两次密码不一致！'];
        }
        $res = $this->employeeService->addPermUser($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/user_list",
     *     tags={"OA权限管理"},
     *     summary="获取员工列表",
     *     description="sang" ,
     *     operationId="user_list",
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
     *         description="用户token",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function userList(){
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
        $res = $this->employeeService->getPermUserList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message, 'data' => $res];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_employee_details",
     *     tags={"OA权限管理"},
     *     summary="获取员工详情",
     *     description="sang" ,
     *     operationId="get_employee_details",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="employee_id",
     *         in="query",
     *         description="员工ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getEmployeeDetails(){
        $rules = [
            'employee_id'       => 'required|integer',
        ];
        $messages = [
            'employee_id.required'      => '员工ID不能为空',
            'employee_id.integer'       => '员工ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->getEmployeeDetails($this->request['employee_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/is_disabled",
     *     tags={"OA权限管理"},
     *     summary="禁用或开启员工",
     *     description="sang" ,
     *     operationId="is_disabled",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="employee_id",
     *         in="query",
     *         description="员工ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function isDisabled(){
        $rules = [
            'employee_id'       => 'required|integer'
        ];
        $messages = [
            'employee_id.required'      => '员工ID不能为空',
            'employee_id.integer'       => '员工ID为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->isDisabled($this->request['employee_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/delete_user",
     *     tags={"OA权限管理"},
     *     summary="删除员工",
     *     description="sang" ,
     *     operationId="delete_user",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="employee_id",
     *         in="query",
     *         description="员工ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function deleteUser(){
        $rules = [
            'employee_id'       => 'required|integer'
        ];
        $messages = [
            'employee_id.required'      => '员工ID不能为空',
            'employee_id.integer'       => '员工ID为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->employeeService->deleteUser($this->request['employee_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_user",
     *     tags={"OA权限管理"},
     *     summary="修改员工",
     *     description="sang" ,
     *     operationId="edit_user",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="用户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="用户名",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="real_name",
     *         in="query",
     *         description="名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="head_portrait",
     *         in="query",
     *         description="头像【URL】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="old_password",
     *         in="query",
     *         description="旧密码，更换密码必须填写",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="new_password",
     *         in="query",
     *         description="新密码，更换密码必须填写",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="confirm_new_password",
     *         in="query",
     *         description="确认新密码",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="roles",
     *         in="query",
     *         description="角色ID，【EG：1,2,13,】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="permission_ids",
     *         in="query",
     *         description="权限ID，【EG：1,2,13,】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editUser()
    {
        $rules = [
            'id'                    => 'required|integer',
            'username'              => 'required',
            'real_name'             => 'required',
            'email'                 => 'required',
            'mobile'                => 'required',
            'head_portrait'         => 'required',
            'new_password'          => 'min:6|max:20',
            'confirm_new_password'  => 'min:6|max:20',
            'roles'                 => 'regex:/^(\d+[,])*\d+$/',
            'permission_ids'        => 'regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'id.required'                   => '用户ID不能为空',
            'id.integer'                    => '用户ID必须为整数',
            'username.required'             => '请填写用户名',
            'real_name.required'            => '请填写用户名称',
            'email.required'                => '请填写用户邮箱',
            'mobile.required'               => '请填写用户手机号',
            'head_portrait.required'        => '请上传用户头像',
            'new_password.min'              => '新密码长度不能低于6位',
            'new_password.max'              => '新密码长度不能超过20位',
            'roles.regex'                   => '角色ID格式有误',
            'permission_ids.regex'          => '权限ID格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        if (isset($this->request['new_password'])){
            if (!isset($this->request['old_password'])){
                return ['code' => 100, 'message' => '请输入旧密码！'];
            }
            if (!isset($this->request['confirm_new_password'])){
                return ['code' => 100, 'message' => '请确认新密码！'];
            }
            if ($this->request['old_password'] === $this->request['new_password']){
                return ['code' => 100, 'message' => '新密码请不要使用旧密码！'];
            }
            if ($this->request['new_password'] !== $this->request['confirm_new_password']){
                return ['code' => 100, 'message' => '两次新密码不一致！'];
            }
        }
        $res = $this->employeeService->editPermUser($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message];
    }

}