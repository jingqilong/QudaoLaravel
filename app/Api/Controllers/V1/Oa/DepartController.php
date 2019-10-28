<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\DepartmentService;

class DepartController extends ApiController
{
    protected $departmentService;

    /**
     * TestApiController constructor.
     * @param DepartmentService $departmentService
     */
    public function __construct(DepartmentService $departmentService)
    {
        parent::__construct();
        $this->departmentService = $departmentService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_depart",
     *     tags={"OA部门"},
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
        if (!$res){
            return ['code' => 100,'message' => $this->departmentService->error];
        }
        return ['code' => 200,'message' => $this->departmentService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/update_depart",
     *     tags={"OA部门"},
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
     *         description="部门记录id号",
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
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="上级部门id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
            'parent_id.Integer'     => '请正确输入类型',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentService->updateDepart($this->request);
        if (!$res){
            return ['code' => 100,'message' => $this->departmentService->error];
        }
        return ['code' => 200,'message' => $this->departmentService->message];
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/oa/del_depart",
     *     tags={"OA部门"},
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
     *             type="integer",
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
        $res = $this->departmentService->delDepart( $id);
        if ($res['code'] == 1){
            return ['code' => 200,'message' => '删除成功'];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_depart_list",
     *     tags={"OA部门"},
     *     summary="获取部门列表",
     *     operationId="get_depart_list",
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
     *         description="获取部门列表失败",
     *     ),
     * )
     *
     */
    public function getDepartList()
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
        $res = $this->departmentService->getDepartList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if (!$res){
            return ['code' => 100, 'message' => $this->departmentService->error];
        }
        return ['code' => 200,'message' => $this->departmentService->message,'data' => $res];
    }
}