<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\SiteService;

class SiteController extends ApiController
{
    public $siteService;

    /**
     * SiteController constructor.
     * @param $siteService
     */
    public function __construct(SiteService $siteService)
    {
        parent::__construct();
        $this->siteService = $siteService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_site",
     *     tags={"精选活动后台"},
     *     summary="添加活动场地",
     *     description="sang" ,
     *     operationId="add_activity_site",
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
     *         name="title",
     *         in="query",
     *         description="场地标签",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="场地地址",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="场地名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="场地主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="场地图片ID串（例如22,66,44）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="labels",
     *         in="query",
     *         description="场地标签串，使用|分隔",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="scale",
     *         in="query",
     *         description="场地规模（单位：人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addActivitySite(){
        $rules = [
            'title'         => 'required',
            'address'       => 'required',
            'name'          => 'required',
            'theme_id'      => 'required|integer',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'scale'         => 'required|integer',
        ];
        $messages = [
            'title.required'        => '场地标题不能为空',
            'address.required'      => '场地地址不能为空',
            'name.required'         => '场地名称不能为空',
            'theme_id.required'     => '场地主题不能为空',
            'theme_id.integer'      => '场地主题ID必须为整数',
            'image_ids.required'    => '场地图片不能为空',
            'image_ids.regex'       => '场地图片ID串格式有误',
            'scale.required'        => '场地规模不能为空',
            'scale.integer'         => '场地规模格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->siteService->addSite($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->siteService->message];
        }
        return ['code' => 100, 'message' => $this->siteService->error];
    }




    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity_site",
     *     tags={"精选活动后台"},
     *     summary="删除活动场地",
     *     description="sang" ,
     *     operationId="delete_activity_site",
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
     *         description="场地ID",
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
    public function deleteActivitySite(){
        $rules = [
            'id'          => 'required|integer',
        ];
        $messages = [
            'id.required'       => '主题ID不能为空',
            'id.integer'        => '主题ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->siteService->deleteSite($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->siteService->message];
        }
        return ['code' => 100, 'message' => $this->siteService->error];
    }




    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_site",
     *     tags={"精选活动后台"},
     *     summary="修改活动场地",
     *     description="sang" ,
     *     operationId="edit_activity_site",
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
     *         description="场地ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="场地标签",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="场地地址",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="场地名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="场地主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="场地图片ID串（例如22,66,44）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="labels",
     *         in="query",
     *         description="场地标签串，使用|分隔",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="scale",
     *         in="query",
     *         description="场地规模（单位：人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editActivitySite(){
        $rules = [
            'id'            => 'required|integer',
            'title'         => 'required',
            'address'       => 'required',
            'name'          => 'required',
            'theme_id'      => 'required|integer',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'scale'         => 'required|integer',
        ];
        $messages = [
            'id.required'           => '场地ID不能为空',
            'id.integer'            => '场地ID必须为整数',
            'title.required'        => '场地标题不能为空',
            'address.required'      => '场地地址不能为空',
            'name.required'         => '场地名称不能为空',
            'theme_id.required'     => '场地主题不能为空',
            'theme_id.integer'      => '场地主题ID必须为整数',
            'image_ids.required'    => '场地图片不能为空',
            'image_ids.regex'       => '场地图片ID串格式有误',
            'scale.required'        => '场地规模不能为空',
            'scale.integer'         => '场地规模格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->siteService->editSite($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->siteService->message];
        }
        return ['code' => 100, 'message' => $this->siteService->error];
    }




    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_site_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动场地列表",
     *     description="sang" ,
     *     operationId="activity_site_list",
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
    public function activitySiteList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->siteService->getSiteList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->siteService->error];
        }
        return ['code' => 200, 'message' => $this->siteService->message, 'data' => $res];
    }

}