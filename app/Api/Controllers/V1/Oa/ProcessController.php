<?php


namespace App\Api\Controllers\V1\Oa;

/**
 * OA审核流程相关
 */

use App\Api\Controllers\ApiController;
use App\Enums\ProcessPrincipalsEnum;
use App\Services\Oa\ProcessActionsService;
use App\Services\Oa\ProcessCategoriesService;
use App\Services\Oa\ProcessDefinitionService;
use App\Services\Oa\ProcessEventsService;
use App\Services\Oa\ProcessNodeActionService;
use App\Services\Oa\ProcessNodeService;
use App\Services\Oa\ProcessRecordService;
use Illuminate\Support\Facades\Auth;

class ProcessController extends ApiController
{
    protected $processCategoriesService;
    protected $processDefinitionService;
    protected $processNodeService;
    protected $processEventsService;
    protected $processActionsService;
    protected $processNodeActionService;
    protected $processRecordService;

    /**
     * AuditController constructor.
     * @param ProcessCategoriesService $processCategoriesService
     * @param ProcessDefinitionService $processDefinitionService
     * @param ProcessNodeService $processNodeService
     * @param ProcessEventsService $processEventsService
     * @param ProcessActionsService $processActionsService
     * @param ProcessNodeActionService $processNodeActionService
     * @param ProcessRecordService $processRecordService
     */
    public function __construct(ProcessCategoriesService $processCategoriesService,
                                ProcessDefinitionService $processDefinitionService,
                                ProcessNodeService $processNodeService,
                                ProcessEventsService $processEventsService,
                                ProcessActionsService $processActionsService,
                                ProcessNodeActionService $processNodeActionService,
                                ProcessRecordService $processRecordService)
    {
        parent::__construct();
        $this->processCategoriesService = $processCategoriesService;
        $this->processDefinitionService = $processDefinitionService;
        $this->processNodeService       = $processNodeService;
        $this->processEventsService     = $processEventsService;
        $this->processActionsService    = $processActionsService;
        $this->processNodeActionService = $processNodeActionService;
        $this->processRecordService     = $processRecordService;
    }





    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/create_process",
     *     tags={"OA流程"},
     *     summary="创建流程",
     *     description="sang" ,
     *     operationId="create_process",
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
     *          description="流程名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="category_id",
     *          in="query",
     *          description="流程类型ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态 0:非激活 1：激活",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function createProcess(){
        $rules = [
            'name'          => 'required',
            'category_id'   => 'required|integer',
            'status'        => 'required|in:0,1',
        ];
        $messages = [
            'name.required'         => '流程名称不能为空！',
            'category_id.required'  => '类型ID不能为空！',
            'category_id.integer'   => '类型ID必须为数字！',
            'status.active_url'     => '状态不能为空！',
            'status.in'             => '状态取值不在范围内！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processDefinitionService->createProcess($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processDefinitionService->message];
        }
        return ['code' => 100,'message' => $this->processDefinitionService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_process",
     *     tags={"OA流程"},
     *     summary="删除流程",
     *     description="sang" ,
     *     operationId="delete_process",
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
     *          name="id",
     *          in="query",
     *          description="ID",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteProcess(){
        $rules = [
            'id'   => 'required|integer',
        ];
        $messages = [
            'id.required'  => 'ID不能为空！',
            'id.integer'   => 'ID必须为数字！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processDefinitionService->deleteProcess($this->request['id']);
        if ($res){
            return ['code' => 200,'message' => $this->processDefinitionService->message];
        }
        return ['code' => 100,'message' => $this->processDefinitionService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_process",
     *     tags={"OA流程"},
     *     summary="修改流程定义",
     *     description="sang" ,
     *     operationId="edit_process",
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
     *          name="id",
     *          in="query",
     *          description="ID",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="流程名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="category_id",
     *          in="query",
     *          description="流程类型ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态 0:非激活 1：激活",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function editProcess(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'category_id'   => 'required|integer',
            'status'        => 'required|in:0,1',
        ];
        $messages = [
            'id.required'           => 'ID不能为空！',
            'id.integer'            => 'ID必须为整型！',
            'name.required'         => '流程名称不能为空！',
            'category_id.required'  => '类型ID不能为空！',
            'category_id.integer'   => '类型ID必须为整型！',
            'status.active_url'     => '状态不能为空！',
            'status.in'             => '状态取值不在范围内！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processDefinitionService->editProcess($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processDefinitionService->message];
        }
        return ['code' => 100,'message' => $this->processDefinitionService->error];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_process_list",
     *     tags={"OA流程"},
     *     summary="获取流程列表",
     *     description="sang" ,
     *     operationId="get_process_list",
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
    public function getProcessList(){
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
        $res = $this->processDefinitionService->getProcessList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processCategoriesService->error];
        }
        return ['code' => 200,'message' => $this->processCategoriesService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/get_process_detail",
     *     tags={"OA流程"},
     *     summary="获取流程详情",
     *     description="sang" ,
     *     operationId="get_process_detail",
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
     *         name="process_id",
     *         in="query",
     *         description="流程ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getProcessDetail(){
        $rules = [
            'process_id' => 'required|integer'
        ];
        $messages = [
            'process_id.required'   => '流程ID不能为空！',
            'process_id.integer'    => '流程ID必须为整型！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processDefinitionService->getProcessDetail($this->request['process_id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->processNodeService->message];
        }
        return ['code' => 200,'message' => $this->processNodeService->error,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_add_node",
     *     tags={"OA流程"},
     *     summary="流程添加节点",
     *     description="sang" ,
     *     operationId="process_add_node",
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
     *          name="node_actions_result_id",
     *          in="query",
     *          description="节点动作结果ID【如果不填，表示添加第一个节点】",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="process_id",
     *          in="query",
     *          description="流程ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="节点名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="limit_time",
     *          in="query",
     *          description="限定时间（单位：分钟）、不填为不限制",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="icon",
     *          in="query",
     *          description="流程图显示图标",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="节点描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function processAddNode(){
        $rules = [
            'node_actions_result_id'=> 'integer',
            'name'                  => 'required',
            'limit_time'            => 'integer',
            'icon'                  => 'url',
            'description'           => 'string',
        ];
        $messages = [
            'node_actions_result_id.integer'=> '节点动作结果ID必须为整型！',
            'process_id.required'           => '流程ID不能为空！',
            'process_id.integer'            => '流程ID必须为整型！',
            'name.required'                 => '节点名称不能为空！',
            'limit_time.integer'            => '限定时间必须为整型！',
            'icon.url'                      => '流程图显示图标必须为url！',
            'description.string'            => '步骤描述必须为字符串！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->processAddNode($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeService->message];
        }
        return ['code' => 100,'message' => $this->processNodeService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_node",
     *     tags={"OA流程"},
     *     summary="删除流程节点",
     *     description="sang，删除节点只能从最末端的节点依次删除，如果节点在流程的中间位置，将无法删除" ,
     *     operationId="delete_node",
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
     *          name="process_id",
     *          in="query",
     *          description="流程ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="node_id",
     *          in="query",
     *          description="节点ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteNode(){
        $rules = [
            'process_id'    => 'required|integer',
            'node_id'       => 'required|integer',
        ];
        $messages = [
            'process_id.required'   => '流程ID不能为空！',
            'process_id.integer'    => '流程ID必须为整型！',
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整型！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->deleteNode($this->request['process_id'], $this->request['node_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeService->message];
        }
        return ['code' => 100,'message' => $this->processNodeService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_edit_node",
     *     tags={"OA流程"},
     *     summary="流程修改节点",
     *     description="sang" ,
     *     operationId="process_edit_node",
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
     *          name="node_id",
     *          in="query",
     *          description="节点ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="节点名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="limit_time",
     *          in="query",
     *          description="限定时间（单位：分钟）、不填为不限制",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="icon",
     *          in="query",
     *          description="流程图显示图标",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="步骤描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function processEditNode(){
        $rules = [
            'node_id'       => 'required|integer',
            'name'          => 'required',
            'limit_time'    => 'integer',
            'icon'          => 'url',
            'description'   => 'string',
        ];
        $messages = [
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整型！',
            'name.required'         => '节点名称不能为空！',
            'limit_time.integer'    => '限定时间必须为整型！',
            'icon.url'              => '流程图显示图标必须为url！',
            'description.string'    => '步骤描述必须为字符串！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->processEditNode($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeService->message];
        }
        return ['code' => 100,'message' => $this->processNodeService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/node_add_action",
     *     tags={"OA流程"},
     *     summary="给流程节点添加动作",
     *     description="sang" ,
     *     operationId="node_add_action",
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
     *         name="node_id",
     *         in="query",
     *         description="节点ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="action_id",
     *         in="query",
     *         description="动作ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function nodeAddAction(){
        $rules = [
            'node_id'       => 'required|integer',
            'action_id'     => 'required|integer',
        ];
        $messages = [
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整数！',
            'action_id.required'    => '动作ID不能为空！',
            'action_id.regex'       => '动作ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->nodeAddAction($this->request['node_id'],$this->request['action_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }



    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/node_delete_action",
     *     tags={"OA流程"},
     *     summary="流程节点删除动作",
     *     description="sang" ,
     *     operationId="node_delete_action",
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
     *         name="node_action_id",
     *         in="query",
     *         description="节点动作ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function nodeDeleteAction(){
        $rules = [
            'node_action_id'    => 'required|integer',
        ];
        $messages = [
            'node_action_id.required'   => '节点动作ID不能为空！',
            'node_action_id.integer'    => '节点动作ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->nodeDeleteAction($this->request['node_action_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_add_event",
     *     tags={"OA流程"},
     *     summary="流程添加事件",
     *     description="sang" ,
     *     operationId="process_add_event",
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
     *         name="node_id",
     *         in="query",
     *         description="节点ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_result_id",
     *         in="query",
     *         description="节点动作结果ID【注：事件类型为动作结果事件时，为必要参数】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="事件类型：（0：节点事件，1，动作结果事件）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principals_type",
     *         in="query",
     *         description="相关人身份（1、执行人，2、监督人，3，发起人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function processAddEvent(){
        $rules = [
            'node_id'               => 'required|integer',
            'node_action_result_id' => 'integer',
            'event_type'            => 'required|in:0,1',
            'event_id'              => 'required|integer',
            'principals_type'       => 'required|in:1,2,3',
        ];
        $messages = [
            'node_id.required'              => '节点ID不能为空！',
            'node_id.integer'               => '节点ID必须为整数！',
            'node_action_result_id.integer' => '节点动作结果ID必须为整数！',
            'event_type.required'           => '事件类型不能为空！',
            'event_type.in'                 => '事件类型不存在！',
            'event_id.required'             => '事件ID不能为空！',
            'event_id.integer'              => '事件ID必须为整数！',
            'principals_type.required'      => '相关人身份不能为空！',
            'principals_type.in'            => '相关人身份不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->processAddEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/process_delete_event",
     *     tags={"OA流程"},
     *     summary="流程删除事件",
     *     description="sang" ,
     *     operationId="process_delete_event",
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
     *         name="node_action_event_id",
     *         in="query",
     *         description="节点动作事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function processDeleteEvent(){
        $rules = [
            'node_action_event_id'  => 'required|integer',
        ];
        $messages = [
            'node_action_event_id.required' => '节点动作事件ID不能为空！',
            'node_action_event_id.integer'  => '节点动作事件ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->processDeleteEvent($this->request['node_action_event_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_edit_event",
     *     tags={"OA流程"},
     *     summary="流程修改事件",
     *     description="sang" ,
     *     operationId="process_edit_event",
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
     *         name="node_action_event_id",
     *         in="query",
     *         description="节点动作事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principals_type",
     *         in="query",
     *         description="相关人身份（1、执行人，2、监督人，3，发起人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function processEditEvent(){
        $rules = [
            'node_action_event_id'  => 'required|integer',
            'event_id'              => 'required|integer',
            'principals_type'       => 'required|in:1,2,3',
        ];
        $messages = [
            'node_action_event_id.required' => '节点动作事件ID不能为空！',
            'node_action_event_id.integer'  => '节点动作事件ID必须为整数！',
            'event_id.required'             => '事件ID不能为空！',
            'event_id.integer'              => '事件ID必须为整数！',
            'principals_type.required'      => '相关人身份不能为空！',
            'principals_type.in'            => '相关人身份不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->processEditEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_process_event_list",
     *     tags={"OA流程"},
     *     summary="获取流程事件列表",
     *     description="sang" ,
     *     operationId="get_process_event_list",
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
     *         name="node_id",
     *         in="query",
     *         description="节点ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_result_id",
     *         in="query",
     *         description="节点动作结果ID【此值不为空，则获取动作结果事件列表】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getProcessEventList(){
        $rules = [
            'node_id'               => 'required|integer',
            'node_action_result_id' => 'integer',
            'page'                  => 'integer',
            'page_num'              => 'integer',
        ];
        $messages = [
            'node_id.required'              => '节点ID不能为空！',
            'node_id.integer'               => '节点ID必须为整数！',
            'node_action_result_id.integer' => '节点动作结果ID必须为整数！',
            'page.integer'                  => '页码必须为整数',
            'page_num.integer'              => '每页显示条数必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->getProcessEventList($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message,'data' => $res];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/action_add_related",
     *     tags={"OA流程"},
     *     summary="给流程节点动作事件与下一节点",
     *     description="sang" ,
     *     operationId="action_add_related",
     *     deprecated=true,
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
     *         name="action_related_id",
     *         in="query",
     *         description="动作相关ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_ids",
     *         in="query",
     *         description="事件ID组合【例如：1,2】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="next_node_id",
     *         in="query",
     *         description="下一节点ID",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function actionAddRelated(){
        $rules = [
            'action_related_id' => 'required|integer',
            'event_ids'         => 'regex:/^(\d+[,])*\d+$/',
            'next_node_id'      => 'integer',
        ];
        $messages = [
            'action_related_id.required'    => '动作相关ID不能为空！',
            'action_related_id.integer'     => '动作相关ID必须为整数！',
            'event_ids.regex'               => '事件组格式有误！',
            'next_node_id.integer'          => '下一节点必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->actionAddRelated($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
        }
        return ['code' => 100,'message' => $this->processNodeActionService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_record",
     *     tags={"OA流程"},
     *     summary="记录流程进度【测试用】",
     *     description="sang" ,
     *     operationId="process_record",
     *     deprecated=true,
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
     *         name="business_id",
     *         in="query",
     *         description="业务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="process_id",
     *         in="query",
     *         description="流程ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_id",
     *         in="query",
     *         description="当前节点ID【为0时表示给业务添加流程】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit_result",
     *         in="query",
     *         description="审核结果",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit_opinion",
     *         in="query",
     *         description="审核意见",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="流程进度记录失败",),
     * )
     *
     */
    public function processRecord(){
        $rules = [
            'business_id'   => 'required|integer',
            'process_id'    => 'required|integer',
            'node_id'       => 'required|integer',
        ];
        $messages = [
            'business_id.required'  => '业务ID不能为空！',
            'business_id.integer'   => '业务ID必须为整数！',
            'process_id.required'   => '流程ID不能为空！',
            'process_id.integer'    => '流程ID必须为整数！',
            'node_id.required'      => '当前节点ID不能为空！',
            'node_id.integer'       => '当前节点ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $employee = Auth::guard('oa_api')->user();
        $res = $this->processRecordService->addRecord(
            $this->request['business_id'],
            $this->request['process_id'],
            $this->request['node_id'],
            $employee->id,
            $this->request['audit_result'] ?? '',
            $this->request['audit_opinion'] ?? ''
        );
        if ($res){
            return ['code' => 200,'message' => $this->processRecordService->message];
        }
        return ['code' => 100,'message' => $this->processRecordService->error];
    }
}