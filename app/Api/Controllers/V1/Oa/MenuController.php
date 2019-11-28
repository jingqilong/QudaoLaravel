<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\AdminMenuService;

class MenuController extends ApiController
{
    protected $menuService;

    /**
     * TestApiController constructor.
     * @param AdminMenuService $menuService
     */
    public function __construct(AdminMenuService $menuService)
    {
        parent::__construct();
        $this->menuService              = $menuService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_menu",
     *     tags={"OA权限管理"},
     *     summary="添加菜单",
     *     description="sang" ,
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
     *         name="primary_key",
     *         in="query",
     *         description="菜单主键，唯一的",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="菜单类型：0：目录，1：菜单：2：操作",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
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
     *         description="访问路径【oa/login】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="vue_route",
     *         in="query",
     *         description="前端路由",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="method",
     *         in="query",
     *         description="访问方法，只填一个【GET、POST、DELETE、PUT等】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="permission",
     *         in="query",
     *         description="权限标识,【login】",
     *         required=true,
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
            'primary_key'   => 'required',
            'type'          => 'required|in:0,1,2',
            'parent_id'     => 'required|integer',
            'title'         => 'required',
            'icon'          => 'required',
            'method'        => 'in:GET,POST,DELETE,PUT',
            'permission'    => 'required',
        ];
        $messages = [
            'primary_key.required'  => '菜单主键不能为空',
            'type.required'         => '请选择菜单类别',
            'type.in'               => '菜单类别取值不在范围内',
            'parent_id.required'    => '请选择父级菜单',
            'parent_id.integer'     => '父级菜单ID必须为整数',
            'title.required'        => '请输入标题',
            'icon.required'         => '请选择图标',
            'method.in'             => '访问方法不存在',
            'permission.required'   => '权限标识不能为空',
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
     *     path="/api/v1/oa/edit_menu",
     *     tags={"OA权限管理"},
     *     summary="修改菜单",
     *     description="sang" ,
     *     operationId="edit_menu",
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
     *         description="菜单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="primary_key",
     *         in="query",
     *         description="菜单主键，唯一的",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="菜单类型：0：目录，1：菜单：2：操作",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
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
     *         description="访问路径【oa/login】",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="vue_route",
     *         in="query",
     *         description="前端路由",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="method",
     *         in="query",
     *         description="访问方法，只填一个【GET、POST、DELETE、PUT等】",
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
    public function editMenu()
    {
        $rules = [
            'id'            => 'required|integer',
            'primary_key'   => 'required',
            'type'          => 'required|in:0,1,2',
            'parent_id'     => 'required|integer',
            'title'         => 'required',
            'icon'          => 'required',
            'method'        => 'in:GET,POST,DELETE,PUT',
        ];
        $messages = [
            'id.required'           => '菜单ID不能为空',
            'id.integer'            => '菜单ID必须为整数',
            'primary_key.required'  => '菜单主键不能为空',
            'type.required'         => '请选择菜单类别',
            'type.in'               => '菜单类别取值不在范围内',
            'parent_id.required'    => '请选择父级菜单',
            'parent_id.integer'     => '父级菜单ID必须为整数',
            'title.required'        => '请输入标题',
            'icon.required'         => '请选择图标',
            'method.in'             => '访问方法不存在',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->editMenu($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/menu_detail",
     *     tags={"OA权限管理"},
     *     summary="菜单详情",
     *     description="sang" ,
     *     operationId="menu_detail",
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
     *         description="菜单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function menuDetail()
    {
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'           => '菜单ID不能为空',
            'id.integer'            => '菜单ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->menuDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/menu_list",
     *     tags={"OA权限管理"},
     *     summary="获取菜单列表",
     *     description="sang" ,
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
        if ($res === false){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/menu_linkage_list",
     *     tags={"OA权限管理"},
     *     summary="添加菜单使用,父级菜单联动列表",
     *     description="sang" ,
     *     operationId="menu_linkage_list",
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
     *         name="type",
     *         in="query",
     *         description="菜单类型：0：目录，1：菜单：2：操作",
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
    public function menuLinkageList(){
        $rules = [
            'type'          => 'required|in:0,1,2',
        ];
        $messages = [
            'type.required'         => '请选择菜单类型',
            'type.in'               => '菜单类型取值不在范围内',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->menuService->linkageList($this->request['type']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_all_menu_list",
     *     tags={"OA权限管理"},
     *     summary="获取所有菜单列表，用于前端访问api",
     *     description="sang" ,
     *     operationId="get_all_menu_list",
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
    public function getAllMenuList(){
        $res = $this->menuService->getAllMenu();
        if ($res === false){
            return ['code' => 100, 'message' => $this->menuService->error];
        }
        return ['code' => 200, 'message' => $this->menuService->message,'data' => $res];
    }
}