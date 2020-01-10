<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\PastService;

class ActivityPastController extends ApiController
{
    public $activityPastService;

    /**
     * ActivityPastController constructor.
     * @param $activityPastService
     */
    public function __construct(PastService $activityPastService)
    {
        parent::__construct();
        $this->activityPastService = $activityPastService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_activity_detail_over",
     *     tags={"精选活动"},
     *     summary="往期活动",
     *     description="jing" ,
     *     operationId="get_activity_detail_over",
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
     *         description="往期活动id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function getActivityDetailOver(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'       => 'ID不能为空',
            'id.integer'        => 'ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPastService->getActivityPast($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPastService->error];
        }
        return ['code' => 200, 'message' => $this->activityPastService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_past",
     *     tags={"精选活动后台"},
     *     summary="添加往期活动",
     *     description="jing" ,
     *     operationId="add_activity_past",
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
     *         name="activity_id",
     *         in="query",
     *         description="往期活动id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         description="参数，例子：[{'resource_ids':['1185'],'resource_urls':['http://fanyi.youdao.com'],'presentation':'一阵蛋疼，首先你说你只有一个分区，那么你就是在OS X里面装的虚拟机了？ 一个分区是不能装两个系统的少女。 重装系统又不难。。。 其实这种情况我觉得是硬件问题-w- 建议苹果客服咨询。 软件问题应该是系统运行中出现错误而不是开机启动问题。','hidden':'0','top':'1'},{'resource_ids':['888,999'],'resource_urls':['http://fanyi.youdao.com','http://fanyi.youdao.com'],'presentation':'一阵蛋疼，首先你说你只有一个分区，那么你就是在OS X里面装的虚拟机了？ 一个分区是不能装两个系统的少女。 重装系统又不难。。。','hidden':'0','top':'0'}]",
     *         required=true,
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
    public function addActivityPast(){
        $rules = [
            'activity_id'           => 'required|integer',
            'parameters'            => 'required|json',
        ];
        $messages = [
            'activity_id.required'     => '活动ID不能为空',
            'activity_id.integer'      => '活动ID不是整数',
            'parameters.required'      => '参数不能为空',
            'parameters.json'          => '参数不是json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPastService->addActivityPast($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPastService->error];
        }
        return ['code' => 200, 'message' => $this->activityPastService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_past",
     *     tags={"精选活动后台"},
     *     summary="修改往期活动",
     *     description="jing" ,
     *     operationId="edit_activity_past",
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
     *         name="activity_id",
     *         in="query",
     *         description="往期活动id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         description="参数，例子：[{'resource_ids':['1185'],'resource_urls':['http://fanyi.youdao.com'],'presentation':'一阵蛋疼，首先你说你只有一个分区，那么你就是在OS X里面装的虚拟机了？ 一个分区是不能装两个系统的少女。 重装系统又不难。。。 其实这种情况我觉得是硬件问题-w- 建议苹果客服咨询。 软件问题应该是系统运行中出现错误而不是开机启动问题。','hidden':'0','top':'1'},{'resource_ids':['888,999'],'resource_urls':['http://fanyi.youdao.com','http://fanyi.youdao.com'],'presentation':'一阵蛋疼，首先你说你只有一个分区，那么你就是在OS X里面装的虚拟机了？ 一个分区是不能装两个系统的少女。 重装系统又不难。。。','hidden':'0','top':'0'}]",
     *         required=true,
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
    public function editActivityPast(){
        $rules = [
            'activity_id'           => 'required|integer',
            'parameters'            => 'required|json',
        ];
        $messages = [
            'activity_id.required'  => '活动ID不能为空',
            'activity_id.integer'   => '活动ID不是整数',
            'parameters.required'   => '参数不能为空',
            'parameters.json'       => '参数不是json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPastService->editActivityPast($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPastService->error];
        }
        return ['code' => 200, 'message' => $this->activityPastService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/activity/del_activity_past",
     *     tags={"精选活动后台"},
     *     summary="删除往期活动",
     *     description="jing" ,
     *     operationId="del_activity_past",
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
     *         name="id",
     *         in="query",
     *         description="ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function delActivityPast(){
        $rules = [
            'id'           => 'required|integer',
        ];
        $messages = [
            'id.required'  => 'ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPastService->delActivityPast($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPastService->error];
        }
        return ['code' => 200, 'message' => $this->activityPastService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/activity/get_activity_past_list",
     *     tags={"精选活动后台"},
     *     summary="oa 获取往期活动列表",
     *     description="jing" ,
     *     operationId="get_activity_past_list",
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
     *      @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败！",
     *     ),
     * )
     *
     */
    public function getActivityPastList(){
        $rules = [
            'activity_id'     => 'required|integer',
        ];
        $messages = [
            'activity_id.required'   => '活动ID不能为空',
            'activity_id.integer'    => '活动ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityPastService->getActivityPastList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->activityPastService->error];
        }
        return ['code' => 200, 'message' => $this->activityPastService->message,'data' => $res];
    }
}