<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessActionResultsService;
use App\Services\Oa\ProcessActionsService;

class ProcessActionsController extends ApiController
{
    protected $processActionsService;
    protected $processActionResultsService;

    /**
     * ProcessActionsController constructor.
     * @param ProcessActionsService $processActionsService
     * @param ProcessActionResultsService $processActionResultsService
     */
    public function __construct(ProcessActionsService $processActionsService,ProcessActionResultsService $processActionResultsService)
    {
        parent::__construct();
        $this->processActionsService = $processActionsService;
        $this->processActionResultsService = $processActionResultsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/add_action",
     *     tags={"OA流程"},
     *     summary="添加动作",
     *     description="sang" ,
     *     operationId="add_action",
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
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="动作名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="动作状态（1:启用，2:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="动作描述",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addAction(){
        $rules = [
            'name'          => 'required',
            'status'        => 'required|in:1,2',
            'description'   => 'required',
        ];
        $messages = [
            'name.required'         => '动作名称不能为空！',
            'status.required'       => '动作状态不能为空！',
            'status.in'             => '动作状态值有误！',
            'description.required'  => '动作描述不能为空！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionsService->addAction($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionsService->message];
        }
        return ['code' => 100,'message' => $this->processActionsService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_action",
     *     tags={"OA流程"},
     *     summary="删除动作",
     *     description="sang" ,
     *     operationId="delete_action",
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
     *     @OA\Parameter(
     *          name="action_id",
     *          in="query",
     *          description="动作ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteAction(){
        $rules = [
            'action_id'  => 'required|integer'
        ];
        $messages = [
            'action_id.required'       => '动作ID不能为空！',
            'action_id.integer'        => '动作ID必须为整数！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionsService->deleteAction($this->request['action_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionsService->message];
        }
        return ['code' => 100,'message' => $this->processActionsService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_action",
     *     tags={"OA流程"},
     *     summary="修改动作",
     *     description="sang" ,
     *     operationId="edit_action",
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
     *     @OA\Parameter(
     *          name="action_id",
     *          in="query",
     *          description="动作ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="动作名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="动作状态（1:启用，2:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="动作描述",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function editAction(){
        $rules = [
            'action_id'     => 'required|integer',
            'name'          => 'required',
            'status'        => 'required|in:1,2',
            'description'   => 'required',
        ];
        $messages = [
            'action_id.required'    => '动作ID不能为空！',
            'action_id.integer'     => '动作ID必须为整数！',
            'name.required'         => '动作名称不能为空！',
            'status.required'       => '动作状态不能为空！',
            'status.in'             => '动作状态值有误！',
            'description.required'  => '动作描述不能为空！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionsService->editAction($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionsService->message];
        }
        return ['code' => 100,'message' => $this->processActionsService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_action_list",
     *     tags={"OA流程"},
     *     summary="获取动作列表",
     *     description="sang" ,
     *     operationId="get_action_list",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getActionList(){
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
        $res = $this->processActionsService->getActionList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processActionsService->error];
        }
        return ['code' => 200,'message' => $this->processActionsService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/add_action_result",
     *     tags={"OA流程"},
     *     summary="添加动作结果",
     *     description="sang" ,
     *     operationId="add_action_result",
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
     *     @OA\Parameter(
     *          name="action_id",
     *          in="query",
     *          description="动作ID",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="结果名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addActionResult(){
        $rules = [
            'action_id'     => 'required|integer',
            'name'          => 'required',
        ];
        $messages = [
            'action_id.required'    => '动作ID不能为空！',
            'action_id.integer'     => '动作ID必须为整数！',
            'name.required'         => '动作名称不能为空！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionResultsService->addActionResult($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionResultsService->message];
        }
        return ['code' => 100,'message' => $this->processActionResultsService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_action_result",
     *     tags={"OA流程"},
     *     summary="删除动作结果",
     *     description="sang" ,
     *     operationId="delete_action_result",
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
     *     @OA\Parameter(
     *          name="result_id",
     *          in="query",
     *          description="结果ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteActionResult(){
        $rules = [
            'result_id'     => 'required|integer',
        ];
        $messages = [
            'result_id.required'    => '结果ID不能为空！',
            'result_id.integer'     => '结果ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionResultsService->deleteActionResult($this->request['result_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionResultsService->message];
        }
        return ['code' => 100,'message' => $this->processActionResultsService->error];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_action_result",
     *     tags={"OA流程"},
     *     summary="修改动作结果",
     *     description="sang" ,
     *     operationId="edit_action_result",
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
     *     @OA\Parameter(
     *          name="result_id",
     *          in="query",
     *          description="结果ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="结果名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function editActionResult(){
        $rules = [
            'result_id'     => 'required|integer',
            'name'          => 'required',
        ];
        $messages = [
            'result_id.required'    => '结果ID不能为空！',
            'result_id.integer'     => '结果ID必须为整数！',
            'name.required'         => '结果名称不能为空！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionResultsService->editActionResult($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionResultsService->message];
        }
        return ['code' => 100,'message' => $this->processActionResultsService->error];
    }
}