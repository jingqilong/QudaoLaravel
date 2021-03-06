<?php


namespace App\Api\Controllers\V1\Oa;

/**
 * OA审核流程相关
 */

use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessCategoriesService;
use App\Services\Oa\ProcessDefinitionService;
use App\Services\Oa\ProcessNodeActionService;
use App\Services\Oa\ProcessNodeService;
use App\Services\Oa\ProcessRecordService;

class ProcessController extends ApiController
{
    protected $processCategoriesService;
    protected $processDefinitionService;
    protected $processNodeService;
    protected $processNodeActionService;
    protected $processRecordService;

    /**
     * AuditController constructor.
     * @param ProcessCategoriesService $processCategoriesService
     * @param ProcessDefinitionService $processDefinitionService
     * @param ProcessNodeService $processNodeService
     * @param ProcessNodeActionService $processNodeActionService
     * @param ProcessRecordService $processRecordService
     */
    public function __construct(ProcessCategoriesService $processCategoriesService,
                                ProcessDefinitionService $processDefinitionService,
                                ProcessNodeService $processNodeService,
                                ProcessNodeActionService $processNodeActionService,
                                ProcessRecordService $processRecordService)
    {
        parent::__construct();
        $this->processCategoriesService = $processCategoriesService;
        $this->processDefinitionService = $processDefinitionService;
        $this->processNodeService       = $processNodeService;
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
        $res = $this->processDefinitionService->getProcessList();
        if ($res === false){
            return ['code' => 100,'message' => $this->processDefinitionService->error];
        }
        return ['code' => 200,'message' => $this->processDefinitionService->message,'data' => $res];
    }


    /**
     * @OA\Get(
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
            return ['code' => 100,'message' => $this->processDefinitionService->error];
        }
        return ['code' => 200,'message' => $this->processDefinitionService->message,'data' => [$res]];
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
     * @OA\Post(
     *     path="/api/v1/oa/process/process_choose_node",
     *     tags={"OA流程"},
     *     summary="流程选择节点",
     *     description="sang，如果动作结果是回到上一个节点，调用此接口" ,
     *     operationId="process_choose_node",
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
     *          description="节点动作结果ID",
     *          required=true,
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
     *          name="node_id",
     *          in="query",
     *          description="节点ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function processChooseNode(){
        $rules = [
            'node_actions_result_id'=> 'required|integer',
            'process_id'            => 'required|integer',
            'node_id'               => 'required|integer',
        ];
        $messages = [
            'node_actions_result_id.required'=> '节点动作结果ID不能为空！',
            'node_actions_result_id.integer'=> '节点动作结果ID必须为整型！',
            'process_id.required'           => '流程ID不能为空！',
            'process_id.integer'            => '流程ID必须为整型！',
            'node_id.required'              => '节点ID不能为空！',
            'node_id.integer'               => '节点ID必须为整型！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->processChooseNode($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeService->message];
        }
        return ['code' => 100,'message' => $this->processNodeService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_last_node_transition",
     *     tags={"OA流程"},
     *     summary="删除与上一步节点之间的流转",
     *     description="sang，只能用来删除下一节点与当前节点在同一步骤或在当前步骤之前的流转" ,
     *     operationId="delete_last_node_transition",
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
     *          description="节点动作结果ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function deleteTransition(){
        $rules = [
            'node_actions_result_id'=> 'required|integer',
        ];
        $messages = [
            'node_actions_result_id.required'=> '节点动作结果ID不能为空！',
            'node_actions_result_id.integer'=> '节点动作结果ID必须为整型！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->deleteTransition($this->request['node_actions_result_id']);
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
            'process_id'    => 'required|integer',
            'node_id'       => 'required|integer',
            'name'          => 'required',
            'limit_time'    => 'integer',
            'icon'          => 'url',
            'description'   => 'string',
        ];
        $messages = [
            'process_id.required'   => '节点ID不能为空！',
            'process_id.integer'    => '节点ID必须为整型！',
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
     *     path="/api/v1/oa/process/action_result_choose_status",
     *     tags={"OA流程"},
     *     summary="流程动作结果选择流转状态",
     *     description="sang" ,
     *     operationId="action_result_choose_status",
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
     *          description="节点动作结果ID",
     *          required=true,
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
     *          name="status",
     *          in="query",
     *          description="流转状态，默认1继续，2结束，3终止",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function actionResultChooseStatus(){
        $rules = [
            'node_actions_result_id'=> 'required|integer',
            'process_id'            => 'required|integer',
            'status'                => 'required|in:1,2,3',
        ];
        $messages = [
            'node_actions_result_id.required'   => '节点动作结果ID不能为空！',
            'node_actions_result_id.integer'    => '节点动作结果ID必须为整型！',
            'process_id.required'               => '流程ID不能为空！',
            'process_id.integer'                => '流程ID必须为整型！',
            'status.required'                   => '流转状态不能为空！',
            'status.in'                         => '流转状态不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeService->actionResultChooseStatus($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeService->message];
        }
        return ['code' => 100,'message' => $this->processNodeService->error];
    }
}