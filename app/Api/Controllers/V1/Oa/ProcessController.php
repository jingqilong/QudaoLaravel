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
     *     path="/api/v1/oa/process/add_process_categories",
     *     tags={"OA流程"},
     *     summary="添加流程分类",
     *     description="sang" ,
     *     operationId="add_process_categories",
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
     *          description="流程类型名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="getway_type",
     *          in="query",
     *          description="网关类型：默认0：路由, 1: 仓库, 2: 资源，3：服务",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="getway_name",
     *          in="query",
     *          description="网关名称：可以是：路由字串，repository.method,    resource",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态 0:非激活状态 1：激活状态",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addProcessCategories()
    {
        $rules = [
            'name'          => 'required',
            'getway_type'   => 'in:0,1,2,3',
            'status'        => 'required|in:0,1',
        ];
        $messages = [
            'name.required'     => '类型名称不能为空！',
            'getway_type.in'    => '网关类型取值不在范围内！',
            'status.active_url' => '状态不能为空！',
            'status.in'         => '状态取值不在范围内！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processCategoriesService->addCategories($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processCategoriesService->message];
        }
        return ['code' => 100,'message' => $this->processCategoriesService->error];
    }



    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_process_categories",
     *     tags={"OA流程"},
     *     summary="删除流程分类",
     *     description="sang" ,
     *     operationId="delete_process_categories",
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
     *          description="流程类型ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败！",),
     * )
     *
     */
    public function deleteProcessCategories(){
        $rules = [
            'id'          => 'required|integer',
        ];
        $messages = [
            'id.required'       => '类型ID不能为空！',
            'id.integer'        => '类型ID必须为数字！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processCategoriesService->deleteCategories($this->request['id']);
        if ($res){
            return ['code' => 200,'message' => $this->processCategoriesService->message];
        }
        return ['code' => 100,'message' => $this->processCategoriesService->error];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_process_categories",
     *     tags={"OA流程"},
     *     summary="修改流程分类",
     *     description="sang" ,
     *     operationId="edit_process_categories",
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
     *          description="流程类型ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="流程类型名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="getway_type",
     *          in="query",
     *          description="网关类型：默认0：路由, 1: 仓库, 2: 资源，3：服务",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="getway_name",
     *          in="query",
     *          description="网关名称：可以是：路由字串，repository.method,    resource",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态 0:非激活状态 1：激活状态",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function editProcessCategories()
    {
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'getway_type'   => 'in:0,1,2,3',
            'status'        => 'required|in:0,1',
        ];
        $messages = [
            'id.required'       => '类型ID不能为空！',
            'id.integer'        => '类型ID必须为数字！',
            'name.required'     => '类型名称不能为空！',
            'getway_type.in'    => '网关类型取值不在范围内！',
            'status.active_url' => '状态不能为空！',
            'status.in'         => '状态取值不在范围内！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processCategoriesService->editCategories($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processCategoriesService->message];
        }
        return ['code' => 100,'message' => $this->processCategoriesService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_categories_list",
     *     tags={"OA流程"},
     *     summary="获取流程分类列表",
     *     description="sang" ,
     *     operationId="get_categories_list",
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
    public function getCategoriesList(){
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
        $res = $this->processCategoriesService->getCategoriesList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processCategoriesService->error];
        }
        return ['code' => 200,'message' => $this->processCategoriesService->message,'data' => $res];
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
     *          description="状态 INACTIVE:非激活 ACTIVE：激活",
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
            'status'        => 'required|in:INACTIVE,ACTIVE',
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
     *          description="状态 INACTIVE:非激活 ACTIVE：激活",
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
            'status'        => 'required|in:INACTIVE,ACTIVE',
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
     *          name="action_related_id",
     *          in="query",
     *          description="节点动作相关ID【如果不填，表示添加第一个节点】",
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
     *          description="限定时间（单位：s）、不填为不限制",
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
            'action_related_id' => 'integer',
            'name'              => 'required',
            'limit_time'        => 'integer',
            'icon'              => 'url',
            'description'       => 'string',
        ];
        $messages = [
            'action_related_id.integer'     => '节点动作相关ID必须为整型！',
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
     *     description="sang" ,
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
     *          description="限定时间（单位：s）、不填为不限制",
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
            'process_id'    => 'required|integer',
            'name'          => 'required',
            'limit_time'    => 'integer',
            'icon'          => 'url',
            'description'   => 'string',
        ];
        $messages = [
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整型！',
            'process_id.required'   => '流程ID不能为空！',
            'process_id.integer'    => '流程ID必须为整型！',
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
     *     path="/api/v1/oa/process/add_event",
     *     tags={"OA流程"},
     *     summary="添加事件",
     *     description="sang" ,
     *     operationId="add_event",
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
     *          description="事件名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="execute",
     *          in="query",
     *          description="事件执行相关字段（例如：send_sms）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="事件状态（ENABLE:启用，DISABLED:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="事件描述",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addEvent(){
        $rules = [
            'name'          => 'required',
            'execute'       => 'required',
            'status'        => 'required|in:ENABLE,DISABLED',
            'description'   => 'required',
        ];
        $messages = [
            'name.required'         => '事件名称不能为空！',
            'execute.required'      => '执行字段不能为空！',
            'status.required'       => '事件状态不能为空！',
            'status.in'             => '事件状态值有误！',
            'description.required'  => '事件描述不能为空！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->addEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_event",
     *     tags={"OA流程"},
     *     summary="删除事件",
     *     description="sang" ,
     *     operationId="delete_event",
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
     *          name="event_id",
     *          in="query",
     *          description="事件ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteEvent(){
        $rules = [
            'event_id'  => 'required|integer'
        ];
        $messages = [
            'event_id.required'       => '事件ID不能为空！',
            'event_id.integer'        => '事件ID必须为整数！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->deleteEvent($this->request['event_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_event",
     *     tags={"OA流程"},
     *     summary="修改事件",
     *     description="sang" ,
     *     operationId="edit_event",
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
     *          name="event_id",
     *          in="query",
     *          description="事件ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="事件名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="execute",
     *          in="query",
     *          description="事件执行相关字段（例如：send_sms）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="事件状态（ENABLE:启用，DISABLED:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="事件描述",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function editEvent(){
        $rules = [
            'event_id'      => 'required|integer',
            'name'          => 'required',
            'execute'       => 'required',
            'status'        => 'required|in:ENABLE,DISABLED',
            'description'   => 'required',
        ];
        $messages = [
            'event_id.required'     => '事件ID不能为空！',
            'event_id.integer'      => '事件ID必须为整数！',
            'name.required'         => '事件名称不能为空！',
            'execute.required'      => '执行字段不能为空！',
            'status.required'       => '事件状态不能为空！',
            'status.in'             => '事件状态值有误！',
            'description.required'  => '事件描述不能为空！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->editEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_event_list",
     *     tags={"OA流程"},
     *     summary="获取事件列表",
     *     description="sang" ,
     *     operationId="get_event_list",
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
    public function getEventList(){
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
        $res = $this->processEventsService->getEventList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processEventsService->error];
        }
        return ['code' => 200,'message' => $this->processEventsService->message, 'data' => $res];
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
     *          name="result",
     *          in="query",
     *          description="动作执行结果（例如：pass,no_pass）请使用逗号分隔",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="动作状态（ENABLE:启用，DISABLED:禁用）",
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
            'result'       => 'required',
            'status'        => 'required|in:ENABLE,DISABLED',
            'description'   => 'required',
        ];
        $messages = [
            'name.required'         => '动作名称不能为空！',
            'result.required'       => '动作执行结果不能为空！',
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
     *          name="result",
     *          in="query",
     *          description="动作执行结果（例如：pass,no_pass）请使用逗号分隔",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="动作状态（ENABLE:启用，DISABLED:禁用）",
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
    public function editAction(){
        $rules = [
            'action_id'      => 'required|integer',
            'name'          => 'required',
            'result'       => 'required',
            'status'        => 'required|in:ENABLE,DISABLED',
            'description'   => 'required',
        ];
        $messages = [
            'action_id.required'    => '动作ID不能为空！',
            'action_id.integer'     => '动作ID必须为整数！',
            'name.required'         => '动作名称不能为空！',
            'result.required'       => '执行结果不能为空！',
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
     *         name="action_ids",
     *         in="query",
     *         description="动作ID组合【例如：1,2】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function nodeAddAction(){
        $rules = [
            'node_id'       => 'required|integer',
            'action_ids'    => 'required|regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整数！',
            'action_ids.required'   => '动作ID不能为空！',
            'action_ids.regex'      => '动作ID串格式有误！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->nodeAddAction($this->request['node_id'],$this->request['action_ids']);
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function nodeDeleteAction(){
        $rules = [
            'node_id'       => 'required|integer',
            'action_id'     => 'required|integer',
        ];
        $messages = [
            'node_id.required'      => '节点ID不能为空！',
            'node_id.integer'       => '节点ID必须为整数！',
            'action_id.required'    => '动作ID不能为空！',
            'action_id.integer'     => '动作ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processNodeActionService->nodeDeleteAction($this->request['node_id'],$this->request['action_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processNodeActionService->message];
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


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/action_add_principal",
     *     tags={"OA流程"},
     *     summary="流程节点动作添加负责人",
     *     description="sang" ,
     *     operationId="action_add_principal",
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
     *     @OA\Parameter(
     *         name="principal_ids",
     *         in="query",
     *         description="负责人ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_iden",
     *         in="query",
     *         description="负责人身份（EXECUTOR:执行人，SUPERVISOR:监督人，AGENT:代理人）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败！",),
     * )
     *
     */
    public function actionAddPrincipal(){
        $rules = [
            'node_action_id'   => 'required|integer',
            'principal_ids'    => 'required|integer',
            'principal_iden'   => 'required|in:'.ProcessPrincipalsEnum::getPrincipalString(),
        ];
        $messages = [
            'node_action_id.required'   => '节点动作ID不能为空！',
            'node_action_id.integer'    => '节点动作ID必须为整数！',
            'principal_ids.required'    => '负责人ID不能为空！',
            'principal_ids.integer'     => '负责人ID必须为整数！',
            'principal_iden.required'   => '负责人身份不能为空！',
            'principal_iden.in'         => '负责人身份有误！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionsService->AddPrincipal($this->request['node_action_id'],$this->request['principal_ids'],$this->request['principal_iden']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionsService->message];
        }
        return ['code' => 100,'message' => $this->processActionsService->error];
    }
}