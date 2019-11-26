<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends ApiController
{
    protected $reservationService;

    /**
     * TestApiController constructor.
     * @param ReservationService $reservationService
     */
    public function __construct(ReservationService $reservationService)
    {
        parent::__construct();
        $this->reservationService = $reservationService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/reservation",
     *     tags={"精选生活"},
     *     summary="预约",
     *     description="sang" ,
     *     operationId="reservation",
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
     *         name="merchant_id",
     *         in="query",
     *         description="商户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="预约人姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="预约人手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="预约人数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="预约时间 （例如：2019-10-01 08:30）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="memo",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="预约失败",
     *     ),
     *     ),
     * )
     *
     */
    public function reservation()
    {
        $rules = [
            'merchant_id'       => 'required|integer',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'time'              => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'number'            => 'required|integer'
        ];
        $messages = [
            'merchant_id.required'  => '商户ID不能为空',
            'merchant_id.integer'   => '商户ID必须为整数',
            'name.required'         => '预约人姓名不能为空',
            'mobile.required'       => '预约人手机号不能为空',
            'mobile.regex'          => '预约人手机号格式有误',
            'time.required'         => '预约时间不能为空',
            'time.regex'            => '预约时间格式有误',
            'number.required'       => '预约人数不能为空',
            'number.integer'        => '预约人数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->reservationService->reservation($this->request,$member->m_id);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/prime/admin/get_reservation_list",
     *     tags={"精选生活商户后台"},
     *     summary="获取预约列表",
     *     description="sang" ,
     *     operationId="get_reservation_list",
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
     *         description="商户 TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索【订单号，预约人姓名，预约人手机号，备注】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="状态",
     *         in="query",
     *         description="状态，默认1正在预约，2预约成功，3预约失败，预约取消",
     *         required=false,
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    protected function getReservationList(){
        $rules = [
            'keywords'      => 'string',
            'state'         => 'in:1,2,3,4',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'state.in'                  => '状态字段取值有误',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $merchant = Auth::guard('prime_api')->user();
        $res = $this->reservationService->reservationList($this->request,$merchant->id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/prime/reservation_list",
     *     tags={"精选生活OA后台"},
     *     summary="获取预约列表",
     *     description="sang" ,
     *     operationId="reservation_list",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索【订单号，预约人姓名，预约人手机号，备注】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="状态，默认1正在预约，2预约成功，3预约失败，4预约取消",
     *         required=false,
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    protected function reservationList(){
        $rules = [
            'keywords'      => 'string',
            'state'         => 'in:1,2,3,4',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'state.in'                  => '状态字段取值有误',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->reservationList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/audit_reservation",
     *     tags={"精选生活商户后台"},
     *     summary="审核预约",
     *     description="sang" ,
     *     operationId="audit_reservation",
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
     *         description="商户 TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="预约ID",
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
    public function auditReservation(){
        $rules = [
            'id'            => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'id.required'               => '预约ID不能为空',
            'id.integer'                => '预约ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $merchant = Auth::guard('prime_api')->user();
        $res = $this->reservationService->auditReservation($this->request['id'],$this->request['audit'],$merchant->id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/audit",
     *     tags={"精选生活OA后台"},
     *     summary="审核预约",
     *     description="sang" ,
     *     operationId="audit",
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
     *         description="OA_TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="预约ID",
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
    public function audit(){
        $rules = [
            'id'            => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'id.required'               => '预约ID不能为空',
            'id.integer'                => '预约ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->auditReservation($this->request['id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/bill_settlement",
     *     tags={"精选生活商户后台"},
     *     summary="账单结算",
     *     description="sang" ,
     *     operationId="bill_settlement",
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
     *         description="商户 TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="预约ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="订单总金额，例如【200，或200.9】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="payment_amount",
     *         in="query",
     *         description="实际支付总金额，例如【199，或200.9】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="消费凭单图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="结算失败",
     *     ),
     * )
     *
     */
    public function billSettlement(){
        $rules = [
            'id'            => 'required|integer',
            'amount'        => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'payment_amount'=> 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'id.required'           => '预约ID不能为空',
            'id.integer'            => '预约ID必须为整数',
            'amount.required'       => '请输入订单总金额',
            'amount.regex'          => '订单总金额格式有误，应为整数或两位小数',
            'payment_amount.required'   => '请输入实际支付总金额',
            'payment_amount.regex'      => '实际支付总金额格式有误，应为整数或两位小数',
            'image_ids.required'    => '请传入消费凭单照片',
            'image_ids.regex'       => '消费凭单图片ID格式有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->billSettlement($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prime/my_reservation_list",
     *     tags={"精选生活"},
     *     summary="获取我的预约列表",
     *     description="sang" ,
     *     operationId="my_reservation_list",
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
     *         description="会员TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="商户类别",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    protected function myReservationList(){
        $rules = [
            'type'          => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'type.integer'              => '商户类别必须为整数',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->myReservationList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/prime/my_reservation_detail",
     *     tags={"精选生活"},
     *     summary="获取我的预约详情",
     *     description="sang" ,
     *     operationId="my_reservation_detail",
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
     *         description="会员TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="预约ID",
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
    protected function myReservationDetail(){
        $rules = [
            'id'            => 'required|integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'id.required'               => '预约ID不能为空',
            'id.integer'                => '预约ID必须为整数',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->myReservationDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/edit_my_reservation",
     *     tags={"精选生活"},
     *     summary="修改我的预约",
     *     description="sang" ,
     *     operationId="edit_my_reservation",
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
     *         name="id",
     *         in="query",
     *         description="预约ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="预约人姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="预约人手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="预约人数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="预约时间 （例如：2019-10-01 08:30）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="memo",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     *     ),
     * )
     *
     */
    public function editMyReservation()
    {
        $rules = [
            'id'                => 'required|integer',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'time'              => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'number'            => 'required|integer'
        ];
        $messages = [
            'id.required'           => '预约ID不能为空',
            'id.integer'            => '预约ID必须为整数',
            'name.required'         => '预约人姓名不能为空',
            'mobile.required'       => '预约人手机号不能为空',
            'mobile.regex'          => '预约人手机号格式有误',
            'time.required'         => '预约时间不能为空',
            'time.regex'            => '预约时间格式有误',
            'number.required'       => '预约人数不能为空',
            'number.integer'        => '预约人数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->editMyReservation($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/prime/cancel_my_reservation",
     *     tags={"精选生活"},
     *     summary="取消我的预约",
     *     description="sang" ,
     *     operationId="cancel_my_reservation",
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
     *         name="id",
     *         in="query",
     *         description="预约ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="取消失败",
     *     ),
     *     ),
     * )
     *
     */
    public function cancelMyReservation()
    {
        $rules = [
            'id'                => 'required|integer',
        ];
        $messages = [
            'id.required'  => '预约ID不能为空',
            'id.integer'   => '预约ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->cancelMyReservation($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }
}