<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Enums\ProcessCategoryEnum;
use App\Services\Oa\BusinessService;

class OaBusinessController extends ApiController
{
    public $businessService;

    /**
     * OaBusinessController constructor.
     * @param $businessService
     */
    public function __construct(BusinessService $businessService)
    {
        parent::__construct();
        $this->businessService = $businessService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_business_process_progress",
     *     tags={"OA流程"},
     *     summary="获取业务流程进度",
     *     description="sang",
     *     operationId="get_business_process_progress",
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
     *         name="business_id",
     *         in="query",
     *         description="业务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="process_category",
     *         in="query",
     *         description="流程类型",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getBusinessProcessProgress(){
        $rules = [
            'business_id'       => 'required|integer',
            'process_category'  => 'required|integer|in:'.ProcessCategoryEnum::getCheckString(),
        ];
        $messages = [
            'business_id.required'          => '业务ID不能为空！',
            'business_id.integer'           => '业务ID必须为整数！',
            'process_category.required'     => '流程类型不能为空！',
            'process_category.integer'      => '流程类型必须为整数！',
            'process_category.in'           => '流程类型不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->businessService->getBusinessProcessProgress($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->businessService->error];
        }
        return ['code' => 200, 'message' => $this->businessService->message,'data' => $res];
    }
}