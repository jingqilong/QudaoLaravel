<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessPerformService;

class ProcessPerformController extends ApiController
{

    public $processPerformService;

    /**
     * ProcessPerformController constructor.
     * @param $processPerformService
     */
    public function __construct(ProcessPerformService $processPerformService)
    {
        parent::__construct();
        $this->processPerformService = $processPerformService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/submit_operation_result",
     *     tags={"OA"},
     *     summary="提交流程操作（审核）结果",
     *     description="sang" ,
     *     operationId="submit_operation_result",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="process_record_id",
     *         in="query",
     *         description="流程记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_actions_result_id",
     *         in="query",
     *         description="节点动作结果ID，（审核结果ID）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="note",
     *         in="query",
     *         description="备注，（审核意见）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function submitOperationResult(){
        $rules = [
            'process_record_id'         => 'required|integer',
            'node_actions_result_id'    => 'required|integer',
            'note'                      => 'max:150',
        ];
        $messages = [
            'process_record_id.required'        => '流程记录ID不能为空！',
            'process_record_id.integer'         => '流程记录ID必须为整数！',
            'node_actions_result_id.required'   => '节点动作结果ID不能为空！',
            'node_actions_result_id.integer'    => '节点动作结果ID必须为整数！',
            'note.max'                          => '备注不能超过150字符',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processPerformService->submitOperationResult($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->processPerformService->error];
        }
        return ['code' => 200, 'message' => $this->processPerformService->message, 'data' => $res];
    }
}