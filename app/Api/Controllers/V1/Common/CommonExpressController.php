<?php


namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Services\Common\ExpressService;

class CommonExpressController extends ApiController
{
    public $commonExpressServices;

    /**
     * CommonExpressController constructor.
     * @param $commonExpressServices
     */
    public function __construct(ExpressService $commonExpressServices)
    {
        parent::__construct();
        $this->commonExpressServices = $commonExpressServices;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_order_express_details",
     *     tags={"公共"},
     *     summary="OA根据订单号获取物流状态",
     *     description="jing" ,
     *     operationId="get_order_express_details",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="快递单号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getOrderExpressDetails(){
        $rules = [
            'code'      => 'required|string',
            'number'    => 'required|string',
        ];
        $messages = [
            'code.required'     => '请输入快递公司编码',
            'code.string'       => '快递公司格式错误',
            'number.required'   => '请输入快递单号',
            'number.string'     => '快递单号格式错误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonExpressServices->getOaOrderExpressDetails($this->request['code'],$this->request['number']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonExpressServices->error];
        }
        return ['code' => 200, 'message' => $this->commonExpressServices->message,'data' => $res];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/common/get_express_list",
     *     tags={"公共"},
     *     summary="获取快递列表",
     *     description="jing" ,
     *     operationId="get_express_list",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getExpressList(){
        $res = $this->commonExpressServices->getExpressList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonExpressServices->error];
        }
        return ['code' => 200, 'message' => $this->commonExpressServices->message,'data' => $res];
    }
}