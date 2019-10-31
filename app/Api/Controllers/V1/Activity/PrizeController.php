<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\PrizeService;

class PrizeController extends ApiController
{
    public $activityPrizeService;

    /**
     * PrizeController constructor.
     * @param $activityPrizeService
     */
    public function __construct(PrizeService $activityPrizeService)
    {
        parent::__construct();
        $this->activityPrizeService = $activityPrizeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_add_prize",
     *     tags={"精选活动后台"},
     *     summary="活动添加奖品",
     *     description="sang" ,
     *     operationId="activity_add_prize",
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
     *         description="token",
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
     *         description="奖品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="奖品标签（一等奖、二等奖、三等奖...）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="奖品数量（0表示无数量限制）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="odds",
     *         in="query",
     *         description="中奖率",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="奖品图片ID组",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="worth",
     *         in="query",
     *         description="奖品价值（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="奖品链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function activityAddPrize(){
        $rules = [
            'activity_id'   => 'required|integer',
            'name'          => 'required',
            'title'         => 'required',
            'number'        => 'required|integer',
            'odds'          => 'required|integer',
            'image_ids'     => 'required:regex:/^(\d+[,])*\d+$/',
            'worth'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'link'          => 'url',
        ];
        $messages = [
            'activity_id.required'      => '活动ID不能为空',
            'activity_id.integer'       => '活动ID必须为整数',
            'name.required'             => '奖品名称不能为空',
            'title.required'            => '奖品标签不能为空',
            'number.required'           => '奖品数量不能为空',
            'number.integer'            => '奖品数量必须为整数',
            'odds.required'             => '奖品中奖率不能为空',
            'odds.integer'              => '奖品中奖率必须为整数',
            'image_ids.required'        => '奖品图片不能为空',
            'image_ids.regex'           => '奖品图片ID组格式有误',
            'worth.required'            => '奖品价值不能为空',
            'worth.regex'               => '奖品价值格式有误',
            'link.url'                  => '奖品链接必须是一个url',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->addPrize($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->activityPrizeService->message];
        }
        return ['code' => 100, 'message' => $this->activityPrizeService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/activity/activity_delete_prize",
     *     tags={"精选活动后台"},
     *     summary="删除活动奖品",
     *     description="sang" ,
     *     operationId="activity_delete_prize",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="奖品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function activityDeletePrize(){
        $rules = [
            'id'   => 'required|integer',
        ];
        $messages = [
            'id.required'      => '奖品ID不能为空',
            'id.integer'       => '奖品ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->deletePrize($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->activityPrizeService->message];
        }
        return ['code' => 100, 'message' => $this->activityPrizeService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_edit_prize",
     *     tags={"精选活动后台"},
     *     summary="修改奖品信息",
     *     description="sang" ,
     *     operationId="activity_edit_prize",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="奖品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
     *         description="奖品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="奖品标签（一等奖、二等奖...）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="奖品数量（0表示无数量限制）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="odds",
     *         in="query",
     *         description="中奖率",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="奖品图片ID组",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="worth",
     *         in="query",
     *         description="奖品价值（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="奖品链接",
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
    public function activityEditPrize(){
        $rules = [
            'id'            => 'required|integer',
            'activity_id'   => 'required|integer',
            'name'          => 'required',
            'title'         => 'required',
            'number'        => 'required|integer',
            'odds'          => 'required|integer',
            'image_ids'     => 'required:regex:/^(\d+[,])*\d+$/',
            'worth'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'link'          => 'url',
        ];
        $messages = [
            'id.required'               => '奖品ID不能为空',
            'id.integer'                => '奖品ID必须为整数',
            'activity_id.required'      => '活动ID不能为空',
            'activity_id.integer'       => '活动ID必须为整数',
            'name.required'             => '奖品名称不能为空',
            'title.required'            => '奖品标签不能为空',
            'number.required'           => '奖品数量不能为空',
            'number.integer'            => '奖品数量必须为整数',
            'odds.required'             => '奖品中奖率不能为空',
            'odds.integer'              => '奖品中奖率必须为整数',
            'image_ids.required'        => '奖品图片不能为空',
            'image_ids.regex'           => '奖品图片ID组格式有误',
            'worth.required'            => '奖品价值不能为空',
            'worth.regex'               => '奖品价值格式有误',
            'link.url'                  => '奖品链接必须是一个url',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->editPrize($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->activityPrizeService->message];
        }
        return ['code' => 100, 'message' => $this->activityPrizeService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_prize_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动奖品列表",
     *     description="sang" ,
     *     operationId="get_prize_list",
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
     *         description="token",
     *         required=true,
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
    public function getPrizeList(){
        $rules = [
            'activity_id'   => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'activity_id.integer'       => '活动ID必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->getPrizeList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPrizeService->error];
        }
        return ['code' => 200, 'message' => $this->activityPrizeService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_winning_list",
     *     tags={"精选活动后台"},
     *     summary="获取中奖列表",
     *     description="sang" ,
     *     operationId="get_winning_list",
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
     *         description="token",
     *         required=true,
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
    public function getWinningList(){
        $rules = [
            'activity_id'   => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'activity_id.integer'       => '活动ID必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPrizeService->getWinningList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPrizeService->error];
        }
        return ['code' => 200, 'message' => $this->activityPrizeService->message, 'data' => $res];
    }
}