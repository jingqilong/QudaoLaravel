<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\AdminRolesService;

class RolesController extends ApiController
{
    protected $adminRolesService;

    /**
     * TestApiController constructor.
     * @param AdminRolesService $adminRolesService
     */
    public function __construct(AdminRolesService $adminRolesService)
    {
        parent::__construct();
        $this->adminRolesService        = $adminRolesService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_roles",
     *     tags={"OA权限管理"},
     *     summary="添加角色",
     *     description="sang" ,
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
     *     @OA\Parameter(
     *         name="menu_ids",
     *         in="query",
     *         description="菜单ID，【EG：1,2,13】",
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
            'menu_ids'          => 'regex:/^([0-9]+[,])*[0-9]+$/',
        ];
        $messages = [
            'slug.required'         => '请填写权限标识',
            'name.required'         => '请填写权限名称',
            'permission_ids.regex'  => '权限ID格式有误',
            'menu_ids.regex'        => '菜单ID格式有误',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminRolesService->addRoles($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/oa/delete_roles",
     *     tags={"OA权限管理"},
     *     summary="删除角色",
     *     description="sang" ,
     *     operationId="delete_roles",
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
     *         name="role_id",
     *         in="query",
     *         description="角色ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function deleteRoles()
    {
        $rules = [
            'role_id'           => 'required|integer'
        ];
        $messages = [
            'role_id.required'      => '角色ID不能为空',
            'role_id.integer'       => '角色ID必须为整数'
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminRolesService->deleteRoles($this->request['role_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => $this->adminRolesService->message];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_roles",
     *     tags={"OA权限管理"},
     *     summary="修改角色",
     *     description="sang" ,
     *     operationId="edit_roles",
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
     *         name="id",
     *         in="query",
     *         description="角色ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
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
     *     @OA\Parameter(
     *         name="menu_ids",
     *         in="query",
     *         description="菜单ID，【EG：1,2,13】",
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
    public function editRoles()
    {
        $rules = [
            'id'                => 'required|integer',
            'slug'              => 'required',
            'name'              => 'required',
            'permission_ids'    => 'regex:/^([0-9]+[,])*[0-9]+$/',
            'menu_ids'          => 'regex:/^([0-9]+[,])*[0-9]+$/',
        ];
        $messages = [
            'id.required'           => '角色ID不能为空',
            'id.integer'            => '角色ID必须为整数',
            'slug.required'         => '请填写权限标识',
            'name.required'         => '请填写权限名称',
            'permission_ids.regex'  => '权限ID格式有误',
            'menu_ids.regex'        => '菜单ID格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminRolesService->editRoles($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => $this->adminRolesService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/role_list",
     *     tags={"OA权限管理"},
     *     summary="获取角色列表",
     *     description="sang" ,
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
        $res = $this->adminRolesService->getRoleList();
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => $this->adminRolesService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_role_details",
     *     tags={"OA权限管理"},
         *     summary="获取角色详情",
     *     description="sang" ,
     *     operationId="get_role_details",
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
     *         description="角色ID",
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
    public function getRoleDetails(){
        $rules = [
            'id'            => 'required|integer'
        ];
        $messages = [
            'id.required'       => '角色ID不能为空',
            'id.integer'        => '角色ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->adminRolesService->getRoleDetails($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->adminRolesService->error];
        }
        return ['code' => 200, 'message' => $this->adminRolesService->message, 'data' => $res];
    }
}