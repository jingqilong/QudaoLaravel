<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\GradeOrdersService;
use App\Services\Member\GradeServiceService;

class GradeController extends ApiController
{
    protected $gradeServiceService;
    protected $gradeOrdersService;

    /**
     * GradeController constructor.
     * @param GradeServiceService $gradeServiceService
     * @param GradeOrdersService $gradeOrdersService
     */
    public function __construct(GradeServiceService $gradeServiceService,GradeOrdersService $gradeOrdersService)
    {
        parent::__construct();
        $this->gradeServiceService = $gradeServiceService;
        $this->gradeOrdersService = $gradeOrdersService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_service",
     *     tags={"会员"},
     *     summary="获取等级下的服务详情",
     *     description="sang",
     *     operationId="get_grade_service",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="等级",
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
    public function getGradeService(){
        $rules = [
            'grade'         => 'required',
        ];
        $messages = [
            'grade.required'        => '等级不能为空',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->getGradeService($this->request['grade']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_card_list",
     *     tags={"会员"},
     *     summary="获取等级卡片列表",
     *     description="sang",
     *     operationId="get_grade_card_list",
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
     *         description="会员 token",
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
    public function getGradeCardList(){
        $res = $this->gradeServiceService->getGradeCardList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_apply_detail",
     *     tags={"会员"},
     *     summary="获取等级申请详情",
     *     description="sang",
     *     operationId="get_grade_apply_detail",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="等级",
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
    public function getGradeApplyDetail(){
        $rules = [
            'grade'         => 'required',
        ];
        $messages = [
            'grade.required'        => '等级不能为空',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->getGradeApplyDetail($this->request['grade']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/upgrade_apply",
     *     tags={"会员"},
     *     summary="提交等级升级申请",
     *     description="sang",
     *     operationId="upgrade_apply",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="申请升级等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="年限",
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
    public function upgradeApply(){
        $rules = [
            'grade'         => 'required',
            'time'          => 'required|integer|max:6',
        ];
        $messages = [
            'grade.required'        => '申请升级等级不能为空',
            'time.required'         => '年限不能为空',
            'time.integer'          => '年限必须为整数',
            'time.max'              => '年限不能超过6年',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeOrdersService->upgradeApply($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeOrdersService->error];
        }
        return ['code' => 200, 'message' => $this->gradeOrdersService->message];
    }
}