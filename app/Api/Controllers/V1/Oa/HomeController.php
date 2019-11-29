<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Common\PvService;
use App\Services\Common\ReservationService;
use App\Services\Member\TradesService;
use App\Services\Score\RecordService;

class HomeController extends ApiController
{
    public $pvService;
    public $reservationService;
    public $recordService;
    public $tradesService;

    /**
     * HomeController constructor.
     * @param PvService $pvService
     * @param ReservationService $reservationService
     * @param RecordService $recordService
     * @param TradesService $tradesService
     */
    public function __construct(PvService $pvService,
                                ReservationService $reservationService,
                                RecordService $recordService,
                                TradesService $tradesService)
    {
        parent::__construct();
        $this->pvService            = $pvService;
        $this->reservationService   = $reservationService;
        $this->recordService        = $recordService;
        $this->tradesService        = $tradesService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_site_pv",
     *     tags={"OA"},
     *     summary="获取访问量",
     *     description="sang" ,
     *     operationId="get_site_pv",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型，1、天，2、周，3、月，4、年",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getSitePv(){
        $rules = [
            'type'          => 'required|in:1,2,3,4',
        ];
        $messages = [
            'type.required'     => '类型不能为空',
            'type.in'           => '类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->pvService->getSitePv($this->request['type']);
        if ($res === false){
            return ['code' => 100,'message' => $this->pvService->error];
        }
        return ['code' => 200,'message' => $this->pvService->message,'date' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_reservation_number",
     *     tags={"OA"},
     *     summary="获取预约数量",
     *     description="sang" ,
     *     operationId="get_reservation_number",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getReservationNumber(){
        $res = $this->reservationService->getReservationNumber();
        if ($res === false){
            return ['code' => 100,'message' => $this->reservationService->error];
        }
        return ['code' => 200,'message' => $this->reservationService->message,'date' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_score_statistics_record",
     *     tags={"OA"},
     *     summary="获取积分消费数据",
     *     description="sang" ,
     *     operationId="get_score_statistics_record",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="day",
     *         in="query",
     *         description="天数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getScoreStatisticsRecord(){
        $rules = [
            'day'          => 'required|integer',
        ];
        $messages = [
            'day.required'     => '天数不能为空',
            'day.in'           => '天数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->recordService->getScoreStatisticsRecord($this->request['day']);
        if ($res === false){
            return ['code' => 100,'message' => $this->recordService->error];
        }
        return ['code' => 200,'message' => $this->recordService->message,'date' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_revenue_record",
     *     tags={"OA"},
     *     summary="获取收益数据",
     *     description="sang" ,
     *     operationId="get_revenue_record",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="day",
     *         in="query",
     *         description="天数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getRevenueRecord(){
        $rules = [
            'day'          => 'required|integer',
        ];
        $messages = [
            'day.required'     => '天数不能为空',
            'day.in'           => '天数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->tradesService->getRevenueRecord($this->request['day']);
        if ($res === false){
            return ['code' => 100,'message' => $this->tradesService->error];
        }
        return ['code' => 200,'message' => $this->tradesService->message,'date' => $res];
    }
}