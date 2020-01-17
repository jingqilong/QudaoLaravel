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
    protected $adminPermissionService;
    protected $adminOperationLogService;

    /**
     * TestApiController constructor.
     * @param AdminPermissionsService $adminPermissionService
     * @param AdminOperationLogService $adminOperationLogService
     */
    public function __construct(
        AdminPermissionsService $adminPermissionService,
        AdminOperationLogService $adminOperationLogService)
    {
        parent::__construct();
        $this->adminPermissionService   = $adminPermissionService;
        $this->adminOperationLogService = $adminOperationLogService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_permission",
     *     tags={"OA权限管理"},
     *     summary="添加权限",
     *     description="sang" ,
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
     *         description="HTTP方法，可多个填写【EG：POST,GET,PUT,DELETE】【EG2:POST】",
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
            'http_method'   => 'regex:/^([A-Z]+[,])*[A-Z]+$/',
            'http_path'     => 'regex:/^([a-zA-Z0-9]+[\/])*[a-zA-Z0-9]+$/',
        ];
        $messages = [
            'slug.required'     => '请填写权限标识',
            'name.required'     => '请填写权限名称',
            'http_method.regex' => 'HTTP方法格式有误',
            'http_path.regex'   => 'HTTP路径格式有误,示例：api/v1/ddddd',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminPermissionService->addPermission($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminPermissionService->error];
        }
        return ['code' => 200, 'message' => $this->adminPermissionService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/delete_permission",
     *     tags={"OA权限管理"},
     *     summary="删除权限",
     *     description="sang" ,
     *     operationId="delete_permission",
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
     *         description="权限ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function deletePermission()
    {
        $rules = [
            'id'            => 'required|integer'
        ];
        $messages = [
            'id.required'       => '权限ID不能为空',
            'id.integer'        => '权限ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminPermissionService->deletePermission($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminPermissionService->error];
        }
        return ['code' => 200, 'message' => $this->adminPermissionService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_permission",
     *     tags={"OA权限管理"},
     *     summary="修改权限",
     *     description="sang" ,
     *     operationId="edit_permission",
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
     *         description="权限ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         description="HTTP方法，可多个填写【EG：POST,GET,PUT,DELETE】【EG2:POST】",
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
    public function editPermission()
    {
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'http_method'   => 'regex:/^([A-Z]+[,])*[A-Z]+$/',
            'http_path'     => 'regex:/^([a-zA-Z0-9]+[\/])*[a-zA-Z0-9]+$/',
        ];
        $messages = [
            'id.required'       => '权限ID不能为空',
            'id.integer'        => '权限ID必须为整数',
            'name.required'     => '请填写权限名称',
            'http_method.regex' => 'HTTP方法格式有误',
            'http_path.regex'   => 'HTTP路径格式有误,示例：api/v1/ddddd',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminPermissionService->editPermission($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminPermissionService->error];
        }
        return ['code' => 200, 'message' => $this->adminPermissionService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/permission_list",
     *     tags={"OA权限管理"},
     *     summary="获取权限列表",
     *     description="sang" ,
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
        $res = $this->adminPermissionService->getPermissionList();
        if (!$res){
            return ['code' => 100, 'message' => $this->adminPermissionService->error];
        }
        return ['code' => 200, 'message' => $this->adminPermissionService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/operation_log",
     *     tags={"OA权限管理"},
     *     summary="获取操作日志",
     *     description="sang" ,
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
        $res = $this->adminOperationLogService->getOperationLog();
        if (!$res){
            return ['code' => 100, 'message' => $this->adminOperationLogService->error];
        }
        return ['code' => 200, 'message' => $this->adminOperationLogService->message, 'data' => $res];
    }

}