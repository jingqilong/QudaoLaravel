<?php


namespace App\Api\Controllers\V1\Oa;

/**
 * OA审核流程相关
 */

use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessCategoriesService;
use App\Services\Oa\ProcessDefinitionService;
use App\Services\Oa\ProcessNodeService;

class ProcessController extends ApiController
{
    protected $processCategoriesService;
    protected $processDefinitionService;
    protected $processNodeService;

    /**
     * AuditController constructor.
     * @param ProcessCategoriesService $processCategoriesService
     * @param ProcessDefinitionService $processDefinitionService
     * @param ProcessNodeService $processNodeService
     */
    public function __construct(ProcessCategoriesService $processCategoriesService,
                                ProcessDefinitionService $processDefinitionService,
                                ProcessNodeService $processNodeService)
    {
        parent::__construct();
        $this->processCategoriesService = $processCategoriesService;
        $this->processDefinitionService = $processDefinitionService;
        $this->processNodeService       = $processNodeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/add_process_categories",
     *     tags={"OA流程"},
     *     summary="添加流程分类",
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
     *          description="网关类型：ROUTE：路由, REPOSITORY: 仓库, RESOURCE: 资源",
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
     *          description="状态 INACTIVE:非激活状态 ACTIVE：激活状态",
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
            'getway_type'   => 'in:ROUTE,REPOSITORY,RESOURCE',
            'status'        => 'required|in:INACTIVE,ACTIVE',
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
     *          description="网关类型：ROUTE：路由, REPOSITORY: 仓库, RESOURCE: 资源",
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
     *          description="状态 INACTIVE:非激活状态 ACTIVE：激活状态",
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
            'getway_type'   => 'in:ROUTE,REPOSITORY,RESOURCE',
            'status'        => 'required|in:INACTIVE,ACTIVE',
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
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function getCategoriesList(){
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
     *     @OA\Response(response=100,description="修改失败",),
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
     *     @OA\Response(response=100,description="修改失败",),
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
     *     summary="获取流程列表【待完善】",
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
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function getProcessList(){
        $res = $this->processDefinitionService->getProcessList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processCategoriesService->error];
        }
        return ['code' => 200,'message' => $this->processCategoriesService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_add_node",
     *     tags={"OA流程"},
     *     summary="流程添加节点",
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
     *          name="position",
     *          in="query",
     *          description="步骤位置，即当前节点是第几步",
     *          required=true,
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
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function processAddNode(){
        $rules = [
            'process_id'    => 'required|integer',
            'name'          => 'required',
            'limit_time'    => 'integer',
            'icon'          => 'url',
            'position'      => 'required|integer|min:1',
            'description'   => 'string',
        ];
        $messages = [
            'process_id.required'   => '流程ID不能为空！',
            'process_id.integer'    => '流程ID必须为整型！',
            'name.required'         => '节点名称不能为空！',
            'limit_time.integer'    => '限定时间必须为整型！',
            'icon.url'              => '流程图显示图标必须为url！',
            'position.required'     => '步骤位置不能为空！',
            'position.integer'      => '步骤位置必须为整型！',
            'position.min'          => '步骤位置最小为1！',
            'description.string'    => '步骤描述必须为字符串！'
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
}