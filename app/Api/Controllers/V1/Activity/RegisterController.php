<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\RegisterService;

class RegisterController extends ApiController
{

    public $registerService;

    /**
     * RegisterController constructor.
     * @param $registerService
     */
    public function __construct(RegisterService $registerService)
    {
        parent::__construct();
        $this->registerService = $registerService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_register_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动报名列表",
     *     description="sang" ,
     *     operationId="get_register_list",
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
     *         description="OA_token",
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
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
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
    public function getRegisterList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getRegisterList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/audit_register",
     *     tags={"精选活动后台"},
     *     summary="审核活动报名",
     *     description="sang" ,
     *     operationId="audit_register",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="register_id",
     *         in="query",
     *         description="报名ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit",
     *         in="query",
     *         description="审核结果，1通过，2驳回",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="审核失败",
     *     ),
     * )
     *
     */
    public function auditRegister(){
        $rules = [
            'register_id'   => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'register_id.required'      => '报名ID不能为空',
            'register_id.integer'       => '报名ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->auditRegister($this->request['register_id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message, 'data' => $res];
    }
}