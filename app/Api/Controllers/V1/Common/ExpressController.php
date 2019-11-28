<?php
/**
 * Created By PhpStorm
 * User: jql
 * Date: 2019/11/15
 * Time: 11:19
 */

namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Services\Common\ExpressService;

class ExpressController extends ApiController
{
    public $ExpressService;

    /**
     * ExpressController constructor.
     * @param $ExpressService
     */
    public function __construct(ExpressService $ExpressService)
    {
        parent::__construct();
        $this->ExpressService = $ExpressService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/get_express_details",
     *     tags={"公共"},
     *     summary="用户获取订单物流状态",
     *     description="jing" ,
     *     operationId="get_express_details",
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
     *         name="code",
     *         in="query",
     *         description="快递公司编码",
     *         required=true,
     *         @OA\Schema(
     *             type="srting",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="快递单号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function getExpressDetails(){
        $rules = [
            'code'      => 'required|string',
            'number'    => 'required|string',
        ];
        $messages = [
            'code.required'     => '请输入快递公司编码',
            'code.string'       => '快递公司格式错误',
            'number.required'   => '请输入快递单号',
            'number.string'     => '快递单号格式不正确',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->ExpressService->getExpressDetails($this->request['code'],$this->request['number']);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }

}