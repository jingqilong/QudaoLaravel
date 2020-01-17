<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessCategoriesService;

class ProcessCategoriesController extends ApiController
{
    protected $processCategoriesService;

    /**
     * ProcessCategoriesController constructor.
     * @param $processCategoriesService
     */
    public function __construct(ProcessCategoriesService $processCategoriesService)
    {
        parent::__construct();
        $this->processCategoriesService = $processCategoriesService;
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
        $res = $this->processCategoriesService->getCategoriesList();
        if ($res === false){
            return ['code' => 100,'message' => $this->processCategoriesService->error];
        }
        return ['code' => 200,'message' => $this->processCategoriesService->message,'data' => $res];
    }
}