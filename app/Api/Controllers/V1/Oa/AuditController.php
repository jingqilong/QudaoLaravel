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
     *     @OA\Response(response=100,description="获取员工失败",),
     * )
     *
     */
    public function addAudit()
    {
        $rules = [
            'name'   => 'required',
            'url'    => 'required',
        ];
        $messages = [
            'name.required' => '请填写审核类型名称',
            'url.required'  => '请填写审核链接',
            //'url.regex'     => '请正确填写审核链接',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->auditService->addAudit($this->request);
        if ($res['code'] == 200){
            return ['code' => 200,'data' => $res];
        }
        return ['code' => 100,'message' => $res['message']];
    }

}