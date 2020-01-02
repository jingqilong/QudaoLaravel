<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Enums\ActivityRegisterStatusEnum;
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
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【报名名称、报名手机号、签到码】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="报名状态：1、待支付，2、待评价，3、已完成，4、已取消",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit",
     *         in="query",
     *         description="审核状态：0、待审核，1、已通过，2、已驳回",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
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
            'status'        => 'in:1,2,3,4',
            'audit'         => 'in:0,1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'status.in'             => '报名状态不存在',
            'audit.in'              => '审核状态不存在',
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
     * @OA\Get(
     *     path="/api/v1/activity/get_register_details",
     *     tags={"精选活动后台"},
     *     summary="获取活动报名详情",
     *     description="sang" ,
     *     operationId="get_register_details",
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
     *         name="id",
     *         in="query",
     *         description="报名ID",
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
    public function getRegisterDetails(){
        $rules = [
            'status'        => 'in:1,2,3,4',
            'audit'         => 'in:0,1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'status.in'             => '报名状态不存在',
            'audit.in'              => '审核状态不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getRegisterDetails($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_sign_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动签到列表",
     *     description="sang" ,
     *     operationId="get_sign_list",
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
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【报名名称、报名手机号、签到码】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_sign",
     *         in="query",
     *         description="是否签到，1已签到，2未签到",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
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
    public function getSignList(){
        $rules = [
            'activity_id'   => 'integer',
            'is_sign'       => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'activity_id.integer'   => '活动ID必须为整数',
            'is_sign.in'            => '是否签到取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getRegisterList(array_merge($this->request,['status_arr' => [ActivityRegisterStatusEnum::EVALUATION,ActivityRegisterStatusEnum::COMPLETED]]));
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
        return ['code' => 200, 'message' => $this->registerService->message];
    }




    /**
     * @OA\Post(
     *     path="/api/v1/activity/sign_in",
     *     tags={"精选活动"},
     *     summary="活动签到",
     *     description="sang" ,
     *     operationId="sign_in",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sign_in_code",
     *         in="query",
     *         description="签到码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="签到失败！",
     *     ),
     * )
     *
     */
    public function signIn(){
        $rules = [
            'sign_in_code'   => 'required',
        ];
        $messages = [
            'sign_in_code.required'  => '签到码不能为空！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->sign($this->request['sign_in_code']);
        if ($res){
            return ['code' => 200, 'message' => $this->registerService->message];
        }
        return ['code' => 100, 'message' => $this->registerService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/activity/sign_in_list",
     *     tags={"精选活动"},
     *     summary="获取活动签到列表",
     *     description="sang" ,
     *     operationId="sign_in_list",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function signList(){
        $rules = [
            'activity_id'   => 'required|integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'activity_id.required'  => '活动ID不能为空',
            'activity_id.integer'   => '活动ID必须为整数',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->signList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message, 'data' => $res];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_register",
     *     tags={"精选活动"},
     *     summary="活动报名",
     *     description="sang" ,
     *     operationId="activity_register",
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
     *         description="token【会员】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="报名失败！",
     *     ),
     * )
     *
     */
    public function activityRegister(){
        $rules = [
            'activity_id'   => 'required|integer',
            'name'          => 'required',
            'mobile'        => 'required|regex:/^1[3-9]\d{9}$/',
        ];
        $messages = [
            'activity_id.required'  => '活动ID不能为空！',
            'activity_id.integer'   => '活动ID必须为整数！',
            'name.required'         => '姓名不能为空！',
            'mobile.required'       => '手机号不能为空！',
            'mobile.regex'          => '手机号格式有误！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->register($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->registerService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->registerService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_admission_ticket",
     *     tags={"精选活动"},
     *     summary="获取入场券",
     *     description="sang" ,
     *     operationId="get_admission_ticket",
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
     *         description="会员token",
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
     *     @OA\Response(
     *         response=100,
     *         description="审核失败",
     *     ),
     * )
     *
     */
    public function getAdmissionTicket(){
        $rules = [
            'register_id'   => 'required|integer',
        ];
        $messages = [
            'register_id.required'      => '报名ID不能为空',
            'register_id.integer'       => '报名ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getAdmissionTicket($this->request['register_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_share_qr_code",
     *     tags={"精选活动"},
     *     summary="获取活动分享二维码",
     *     description="sang" ,
     *     operationId="get_share_qr_code",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动ID",
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
    public function getShareQrCode(){
        $rules = [
            'activity_id'   => 'required|integer',
        ];
        $messages = [
            'activity_id.required'      => '活动ID不能为空',
            'activity_id.integer'       => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->registerService->getShareQrCode($this->request['activity_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->registerService->error];
        }
        return ['code' => 200, 'message' => $this->registerService->message,'data' => $res];
    }
}