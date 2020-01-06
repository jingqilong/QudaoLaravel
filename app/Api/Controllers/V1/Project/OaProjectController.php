<?php


namespace App\Api\Controllers\V1\Project;


use App\Api\Controllers\ApiController;
use App\Services\Project\OaProjectService;

class OaProjectController extends ApiController
{

    protected $OaProjectService;

    public function __construct(OaProjectService $OaProjectService)
    {
        parent::__construct();
        $this->OaProjectService = $OaProjectService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/project/get_project_order_list",
     *     tags={"项目对接(h5后台调用)"},
     *     summary="获取项目订单列表(加搜索)",
     *     operationId="get_project_order_list",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【手机号，姓名，项目对接名称】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态【0待审核 1审核通过 2审核失败】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="排序【1 正序 2 倒叙  默认 正序】",
     *         required=false,
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
     *     @OA\Response(response=100,description="获取项目订单列表失败",),
     * )
     *
     */
    public function getProjectOrderList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'status'        => 'in:0,1,2',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
            'status.in'                 => '状态值不存在 ',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->OaProjectService->getProjectOrderList($this->request);

        return ['code' => 200, 'message' => $this->OaProjectService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/project/get_project_order_info",
     *     tags={"项目对接(h5后台调用)"},
     *     summary="获取项目订单信息",
     *     operationId="get_project_order_info",
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
     *         description="OA token",
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
     *     @OA\Response(response=100,description="获取项目订单列表失败",),
     * )
     *
     */
    public function getProjectOrderInfo()
    {
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'        => '请填写ID',
            'id.integer'         => 'ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $id = $this->request['id'];
        $list = $this->OaProjectService->getProjectOrderById($id);
        if (!$list){
            return ['code' => 200, 'message' => $this->OaProjectService->error];
        }
        return ['code' => 200, 'message' => $this->OaProjectService->message,'data' => $list];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/project/set_project_order_status",
     *     tags={"项目对接(h5后台调用)"},
     *     summary="OA员工 设置项目对接订单状态",
     *     operationId="set_project_order_status",
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
     *         description="OA token",
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态 【1审核通过 2审核驳回 】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取项目订单列表失败",),
     * )
     *
     */
    public function setProjectOrderStatus()
    {
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'id.required'        => '请填写ID',
            'id.integer'         => 'ID必须为整数',
            'status.required'    => '请填写状态值',
            'status.in'          => '审核值不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $list = $this->OaProjectService->setProjectOrderStatusById($this->request['id'],$this->request['status']);
        if (!$list){
            return ['code' => 100, 'message' => $this->OaProjectService->error];
        }
        return ['code' => 200, 'message' => $this->OaProjectService->message,'data' => $list];
    }

}