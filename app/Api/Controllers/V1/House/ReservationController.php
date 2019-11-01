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
        $res = $this->reservationService->reservation($this->request,$member->m_id);
        if ($res){
            return ['code' => 200, 'message' => $this->reservationService->message];
        }
        return ['code' => 100, 'message' => $this->reservationService->error];
    }

    /**
     * @OA\get(
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
        $res = $this->reservationService->reservationList($this->request,$member->m_id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->reservationService->error];
        }
        return ['code' => 200, 'message' => $this->reservationService->message, 'data' => $res];
    }
}