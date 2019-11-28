<?php


namespace App\Api\Controllers\V1\Project;


use App\Api\Controllers\ApiController;
use App\Services\Project\ProjectService;

class ProjectController extends ApiController
{

    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        parent::__construct();
        $this->projectService = $projectService;
    }


    /**
     * @OA\Get(
     *     path="/api/v1/project/get_project_list",
     *     tags={"项目对接(前端调用)"},
     *     summary="获取项目订单列表",
     *     operationId="get_project_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
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
     *     @OA\Response(response=100,description="获取项目订单列表失败",),
     * )
     *
     */
    public function getProjectList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer'
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->projectService->getProjectList($this->request);
        if ($list){
            return ['code' => 200, 'message' => $this->projectService->message,'data' => $list];
        }
        return ['code' => 100, 'message' => $this->projectService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/project/get_project_info",
     *     tags={"项目对接(前端调用)"},
     *     summary="获取项目订单信息",
     *     operationId="get_project_info",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取项目订单失败",),
     * )
     *
     */
    public function getProjectInfo()
    {
        $rules = [
            'id'              => 'required|integer',
        ];
        $messages = [
            'id.required'             => '请传值id',
            'id.integer'              => '请正确传值id',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id   = $this->request['id'];
        $list = $this->projectService->getProjectInfo($id);
        if (!$list){
            return ['code' => 100, 'message' => $this->projectService->error];
        }
        return ['code' => 200, 'message' => $this->projectService->message,'data' => $list];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/project/add_project",
     *     tags={"项目对接(前端调用)"},
     *     summary="添加项目对接订单",
     *     operationId="add_project",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="姓名",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="mobile",
     *          in="query",
     *          description="手机号",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="project_name",
     *          in="query",
     *          description="项目名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="reservation_at",
     *          in="query",
     *          description="预约时间",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="remark",
     *          in="query",
     *          description="备注",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加项目订单失败",),
     * )
     *
     */
    public function addProject()
    {
        $rules = [
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'project_name'      => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'project_name.required'     => '请输入项目名称',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->projectService->addProject($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->projectService->error];
        }
        return ['code' => 200, 'message' => $this->projectService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/project/upd_project",
     *     tags={"项目对接(前端调用)"},
     *     summary="修改项目订单信息",
     *     operationId="upd_project",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="订单ID",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="姓名",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="mobile",
     *          in="query",
     *          description="手机号",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="project_name",
     *          in="query",
     *          description="项目名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="reservation_at",
     *          in="query",
     *          description="预约时间",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="remark",
     *          in="query",
     *          description="备注",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="修改项目订单失败",),
     * )
     *
     */
    public function updProject()
    {
        $rules = [
            'id'                => 'required',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'project_name'      => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'id.required'               => '无法获取到订单id',
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'project_name.required'     => '请输入项目名称',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->projectService->updProject($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->projectService->error];
        }
        return ['code' => 200, 'message' => $this->projectService->message];
    }




    /**
     * @OA\Delete(
     *     path="/api/v1/project/del_project",
     *     tags={"项目对接(前端调用)"},
     *     summary="删除项目订单信息",
     *     operationId="del_project",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="项目订单id",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除项目订单失败",),
     * )
     *
     */
    public function delProject()
    {
        $rules = [
            'id'      => 'required',
        ];
        $messages = [
            'id.required'     => '找不到该ID！',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        $res = $this->projectService->delProject($id);
        if (!$res){
            return ['code' => 100, 'message' => $this->projectService->error];
        }
        return ['code' => 200, 'message' => $this->projectService->message];
    }
}