<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends ApiController
{
    public $reservationService;

    /**
     * ReservationController constructor.
     * @param $reservationService
     */
    public function __construct(ReservationService $reservationService)
    {
        parent::__construct();
        $this->reservationService = $reservationService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/house/reservation",
     *     tags={"房产租赁"},
     *     summary="预约看房",
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
     *         name="house_id",
     *         in="query",
     *         description="房产ID",
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
     * )
     *
     */
    public function reservation(){
        $rules = [
            'house_id'          => 'required|integer',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'time'              => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
        ];
        $messages = [
            'house_id.required'     => '房产ID不能为空',
            'house_id.integer'      => '房产ID必须为整数',
            'name.required'         => '预约人姓名不能为空',
            'mobile.required'       => '预约人手机号不能为空',
            'mobile.regex'          => '预约人手机号格式有误',
            'time.required'         => '预约时间不能为空',
            'time.regex'            => '预约时间格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->reservationService->reservation($this->request,$member->id);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/reservation_list",
     *     tags={"房产租赁"},
     *     summary="个人预约列表",
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
     *         description="会员token",
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
    public function reservationList(){
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
        $member = Auth::guard('member_api')->user();
        $res = $this->reservationService->reservationList($this->request,$member->id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/house/all_reservation_list",
     *     tags={"房产租赁后台"},
     *     summary="预约列表",
     *     description="sang" ,
     *     operationId="all_reservation_list",
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
     *         description="状态，默认1待审核，2预约成功，3预约失败",
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
    public function allReservationList(){
        $rules = [
            'keywords'      => 'string',
            'state'         => 'in:1,2,3',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'state.in'              => '状态字段取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
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
     *     path="/api/v1/house/audit_reservation",
     *     tags={"房产租赁后台"},
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
     *         description="OA_token",
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
        $res = $this->reservationService->auditReservation($this->request['id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/house/is_reservation_list",
     *     tags={"房产租赁"},
     *     summary="个人被预约列表",
     *     description="sang" ,
     *     operationId="is_reservation_list",
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
    public function isReservationList(){
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
        $res = $this->reservationService->isReservationList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/get_reservation_detail",
     *     tags={"房产租赁"},
     *     summary="我的预约详情",
     *     description="sang" ,
     *     operationId="get_reservation_detail",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getReservationDetail(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'               => '预约ID不能为空',
            'id.integer'                => '预约ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->getReservationDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/house/cancel_reservation",
     *     tags={"房产租赁"},
     *     summary="取消预约",
     *     description="sang" ,
     *     operationId="cancel_reservation",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function cancelReservation(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'               => '预约ID不能为空',
            'id.integer'                => '预约ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->cancelReservation($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_reservation",
     *     tags={"房产租赁"},
     *     summary="修改预约",
     *     description="sang" ,
     *     operationId="edit_reservation",
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
     * )
     *
     */
    public function editReservation(){
        $rules = [
            'id'                => 'required|integer',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[35678][0-9]{9}$/',
            'time'              => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])(\s| \S )([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
        ];
        $messages = [
            'id.required'           => '预约ID不能为空',
            'id.integer'            => '预约ID必须为整数',
            'name.required'         => '预约人姓名不能为空',
            'mobile.required'       => '预约人手机号不能为空',
            'mobile.regex'          => '预约人手机号格式有误',
            'time.required'         => '预约时间不能为空',
            'time.regex'            => '预约时间格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->reservationService->editReservation($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }
}