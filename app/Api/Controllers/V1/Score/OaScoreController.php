<?php
namespace App\Api\Controllers\V1\Score;

use App\Api\Controllers\ApiController;
use App\Services\Score\RecordService;

class OaScoreController extends ApiController
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
     * @OA\Post(
     *     path="/api/v1/score/give_score",
     *     tags={"积分后台"},
     *     summary="赠送积分",
     *     operationId="give_score",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="会员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score_type",
     *         in="query",
     *         description="积分类别",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="score",
     *         in="query",
     *         description="赠送积分",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="explain",
     *         in="query",
     *         description="积分赠送说明",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="赠送失败",),
     * )
     *
     */
    public function giveScore(){
        $rules = [
            'member_id'         => 'required|integer',
            'score_type'        => 'required|integer',
            'score'             => 'required|integer',
            'explain'           => 'required',
        ];
        $messages = [
            'member_id.required'        => '会员ID不能为空',
            'member_id.integer'         => '会员ID必须为整数',
            'score_type.required'       => '积分类别不能为空',
            'score_type.integer'        => '积分类别必须为整数',
            'score.required'            => '赠送积分不能为空',
            'score.integer'             => '赠送积分必须为整数',
            'explain.required'          => '积分赠送说明不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->giveScore($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/score/get_score_record_list",
     *     tags={"积分后台"},
     *     summary="获取积分记录列表",
     *     operationId="get_score_record_list",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索，【会员姓名、会员手机号、操作说明】",
     *         required=false,
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
     *         name="latest",
     *         in="query",
     *         description="是否最新记录，1【每个成员不同种类积分的最新记录】",
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
    public function getScoreRecordList(){
        $rules = [
            'score_type'        => 'integer',
            'latest'            => 'in:1',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'score_type.integer'    => '积分类别必须为整数',
            'latest.in'             => '是否最新记录取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->getScoreRecordList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/score/get_score_category_list",
     *     tags={"积分后台"},
     *     summary="获取积分分类列表",
     *     operationId="get_score_category_list",
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
     *         description="OA token",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getScoreCategoryList(){
        $rules = [
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->getScoreCategoryList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/score/add_score_category",
     *     tags={"积分后台"},
     *     summary="添加积分分类",
     *     operationId="add_score_category",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="积分名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="expense_rate",
     *         in="query",
     *         description="积分消费汇率，默认1：1积分抵扣1元",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cashing_rate",
     *         in="query",
     *         description="积分提现汇率，默认1：1积分提现1元",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_cashing",
     *         in="query",
     *         description="是否可提现，默认0：不能提现，1可以提现",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="积分状态，默认0开启，1关闭",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addScoreCategory(){
        $rules = [
            'name'              => 'required',
            'expense_rate'      => 'required|float',
            'cashing_rate'      => 'required|float',
            'is_cashing'        => 'required|in:0,1',
            'status'            => 'required|in:0,1',
        ];
        $messages = [
            'name.required'             => '积分分类不能为空',
            'expense_rate.required'     => '消费汇率不能为空',
            'expense_rate.float'        => '消费汇率必须浮点数',
            'cashing_rate.required'     => '提现汇率不能为空',
            'cashing_rate.float'        => '提现汇率必须浮点数',
            'is_cashing.required'       => '是否可提现不能为空',
            'is_cashing.in'             => '是否可提现取值有误',
            'status.required'           => '积分状态不能为空',
            'status.in'                 => '积分状态取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->addScoreCategory($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/score/open_or_close",
     *     tags={"积分后台"},
     *     summary="开启或关闭积分分类",
     *     operationId="open_or_close",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="积分ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function openOrClose(){
        $rules = [
            'id'                => 'required|integer'
        ];
        $messages = [
            'id.required'               => '积分ID不能为空',
            'id.integer'                => '积分ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->scoreService->openOrClose($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->scoreService->error];
        }
        return ['code' => 200, 'message' => $this->scoreService->message];
    }
}