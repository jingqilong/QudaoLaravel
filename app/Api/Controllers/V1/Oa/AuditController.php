<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Shop\AuditService;

class AuditController extends ApiController
{
    protected $auditService;

    /**
     * AuditController constructor.
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        parent::__construct();
        $this->auditService = $auditService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_audit",
     *     tags={"OA流程"},
     *     summary="添加审核类型",
     *     operationId="add_audit",
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
     *          description="审核类型名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="url",
     *          in="query",
     *          description="审核类型链接",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加审核类型失败",),
     * )
     *
     */
    /**
     * @return array
     * @param 添加审核类型
     */
    public function addAudit()
    {
        $rules = [
            'name'   => 'required|unique:oa_audit_type',
            'url'    => 'required|active_url',
        ];
        $messages = [
            'name.required' => '未找到审核类型',
            'name.unique'   => '重复的审核类型，请重新输入',
            'url.active_url'     => '不是有效的网址',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->auditService->addAudit($this->request);
        if ($res['code'] == 200){
            return [$res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/del_audit",
     *     tags={"OA流程"},
     *     summary="删除审核类型",
     *     operationId="del_audit",
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
     *          description="审核类型名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="审核类型id",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除审核类型失败",),
     * )
     *
     */
    /**
     * @return array
     * @param 删除审核类型
     */
    public function delAudit()
    {
        $rules = [
            'id'     => 'required',
            'name'   => 'required',
        ];
        $messages = [
            'name.required' => '未找到审核类型',
            'id.required'   => '未找到审核类型ID',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->auditService->delAudit($this->request);
        if ($res['code'] == 200){
            return [$res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/upd_audit",
     *     tags={"OA流程"},
     *     summary="更新审核类型",
     *     operationId="upd_audit",
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
     *         name="id",
     *         in="query",
     *         description="审核类型id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="审核类型名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="url",
     *          in="query",
     *          description="审核类型链接",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="更新审核类型失败",),
     * )
     *
     */
    /**
     * @return array
     * @param 更新审核类型
     */
    public function updateAudit()
    {
        $rules = [
            'id'     => 'required',
            'name'   => 'required|unique:oa_audit_type',
            'url'    => 'required|active_url',
        ];
        $messages = [
            'name.required' => '未找到审核类型',
            'name.unique'   => '重复的审核类型，请重新输入',
            'id.required'   => '未找到审核类型ID',
            'url.active_url'  => '不是有效的网址',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->auditService->updateAudit($this->request);
        if ($res['code'] == 200){
            return [$res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_audit",
     *     tags={"OA流程"},
     *     summary="获取审核类型",
     *     operationId="get_audit",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    /**
     * @return array
     */
    public function getAudit()
    {
        $res = $this->auditService->getAudit();
        if ($res['code'] == 200){
            return [ $res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

}