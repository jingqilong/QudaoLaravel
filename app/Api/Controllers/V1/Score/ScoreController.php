<?php
namespace App\Api\Controllers\V1\Score;

use App\Api\Controllers\ApiController;
use App\Services\Score\RecordService;
use Illuminate\Support\Facades\Auth;

class ScoreController extends ApiController
{
    public $scoreService;

    /**
     * OaScoreController constructor.
     * @param $scoreService
     */
    public function __construct(RecordService $scoreService)
    {
        parent::__construct();
        $this->scoreService = $scoreService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/score/get_my_score",
     *     tags={"积分"},
     *     summary="获取我的积分",
     *     operationId="get_my_score",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getMyScore(){
        $member = Auth::guard('member_api')->user();
        $res = $this->scoreService->getMyScore($member->id);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/score/get_my_record_list",
     *     tags={"积分"},
     *     summary="获取我的积分记录列表",
     *     operationId="get_my_record_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *         name="score_type",
     *         in="query",
     *         description="积分类型",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getMyRecordList(){
        $rules = [
            'score_type'        => 'integer',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'score_type.integer'    => '积分类别必须为整数',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->getMyRecordList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message,'data' => $res];
    }
}