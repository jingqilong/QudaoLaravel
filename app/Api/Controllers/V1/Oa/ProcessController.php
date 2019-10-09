<?php


namespace App\Api\Controllers\V1\Oa;

/**
 * OA审核流程相关
 */

use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessCategoriesService;
use App\Services\Shop\AuditService;

class ProcessController extends ApiController
{
    protected $processCategoriesService;

    /**
     * AuditController constructor.
     * @param ProcessCategoriesService $processCategoriesService
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

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
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
}