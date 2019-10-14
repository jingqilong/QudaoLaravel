<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\AdminMenuService;
use App\Services\Oa\AdminOperationLogService;
use App\Services\Oa\AdminPermissionsService;
use App\Services\Oa\AdminRolesService;
use App\Services\Oa\EmployeeService;

class PermissionsController extends ApiController
{
    protected $menuService;
    protected $employeeService;
    protected $adminPermissionService;
    protected $adminRolesService;
    protected $adminOperationLogService;

    /**
     * TestApiController constructor.
     * @param AdminMenuService $menuService
     * @param EmployeeService $employeeService
     * @param AdminPermissionsService $adminPermissionService
     * @param AdminRolesService $adminRolesService
     * @param AdminOperationLogService $adminOperationLogService
     */
    public function __construct(
        AdminMenuService $menuService,
        EmployeeService $employeeService,
        AdminPermissionsService $adminPermissionService,
        AdminRolesService $adminRolesService,
        AdminOperationLogService $adminOperationLogService)
    {
        parent::__construct();
        $this->menuService              = $menuService;
        $this->employeeService          = $employeeService;
        $this->adminPermissionService   = $adminPermissionService;
        $this->adminRolesService        = $adminRolesService;
        $this->adminOperationLogService = $adminOperationLogService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_menu",
     *     tags={"OA权限管理"},
     *     summary="添加菜单",
     *     operationId="add_menu",
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
     *         name="parent_menu",
     *         in="query",
     *         description="父级菜单id，0为顶级菜单",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon",
     *         in="query",
     *         description="图标",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="path",
     *         in="query",
     *         description="路径【oa/login】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="role_id",
     *         in="query",
     *         description="角色id,【1,2】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="permission",
     *         in="query",
     *         description="权限标识,【login】",
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
    public function addMenu()
    {
        $rules = [
            'parent_menu'   => 'required|integer',
            'title'         => 'required',
            'icon'          => 'required',
            'role_id'       => 'regex:/^([0-9]+[,])*[0-9]+$/',
        ];
        $messages = [
            'parent_menu.required'  => '请选择父级菜单',
            'integer.required'      => '父级菜单ID必须为整数',
            'title.required'        => '请输入标题',
            'icon.required'         => '请选择图标',
            'role_id.regex'         => '角色id格式有误',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->addMenu($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_permission",
     *     tags={"OA权限管理"},
     *     summary="添加权限",
     *     operationId="add_permission",
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
     *         name="slug",
     *         in="query",
     *         description="标识",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="权限名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="http_method",
     *         in="query",
     *         description="HTTP方法，【EG：POST,GET,PUT,DELETE】【EG2:POST】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="http_path",
     *         in="query",
     *         description="HTTP路径，多个路径使用逗号分隔【/menu_list,/add_roles】",
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
    public function addPermission()
    {
        $rules = [
            'slug'          => 'required',
            'name'          => 'required',
            'http_method'   => 'regex:/^([a-zA-Z]+[,])*[a-zA-Z]+$/',
            'http_path'     => 'regex:/^([a-zA-Z/]+[,])*[a-zA-Z/]+$/',
        ];
        $messages = [
            'slug.required'     => '请填写权限标识',
            'name.required'     => '请填写权限名称',
            'http_method.regex' => 'HTTP方法格式有误',
            'http_path.regex'   => 'HTTP路径格式有误',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->addPermission($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_roles",
     *     tags={"OA权限管理"},
     *     summary="添加角色",
     *     operationId="add_roles",
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
     *         name="slug",
     *         in="query",
     *         description="标识",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="角色名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="permission_ids",
     *         in="query",
     *         description="权限ID，【EG：1,2,13】",
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
    public function addRoles()
    {
        $rules = [
            'slug'              => 'required',
            'name'              => 'required',
            'permission_ids'    => 'regex:/^([0-9]+[,])*[0-9]+$/',
        ];
        $messages = [
            'slug.required'         => '请填写权限标识',
            'name.required'         => '请填写权限名称',
            'permission_ids.regex'  => '权限ID格式有误',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->addRoles($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_user",
     *     tags={"OA权限管理"},
     *     summary="添加用户",
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
     *         description="名称",
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
            'password'          => 'required',
            'confirm_password'  => 'required',
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
            'confirm_password.required' => '请填写确认密码',
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
     *     path="/api/v1/oa/menu_list",
     *     tags={"OA权限管理"},
     *     summary="获取菜单列表",
     *     operationId="menu_list",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function menuList(){
        $res = $this->menuService->getMenuList();
        if (!$res){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/user_list",
     *     tags={"OA权限管理"},
     *     summary="获取用户列表",
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
        $res = $this->employeeService->getPermUserList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->employeeService->error];
        }
        return ['code' => 200, 'message' => $this->employeeService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/permission_list",
     *     tags={"OA权限管理"},
     *     summary="获取权限列表",
     *     operationId="permission_list",
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
    public function permissionList(){
        $res = $this->adminPermissionService->getPermissionList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->adminPermissionService->error];
        }
        return ['code' => 200, 'message' => $this->adminPermissionService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/role_list",
     *     tags={"OA权限管理"},
     *     summary="获取角色列表",
     *     operationId="role_list",
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
    public function roleList(){
        $res = $this->adminRolesService->getRoleList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => $this->adminRolesService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/operation_log",
     *     tags={"OA权限管理"},
     *     summary="获取操作日志",
     *     operationId="operation_log",
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
    public function operationLog(){
        $res = $this->adminOperationLogService->getOperationLog(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->adminOperationLogService->error];
        }
        return ['code' => 200, 'message' => $this->adminOperationLogService->message, 'data' => $res];
    }
}