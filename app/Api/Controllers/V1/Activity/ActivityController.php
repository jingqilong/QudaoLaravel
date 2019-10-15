<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\PrizeService;
use App\Services\Activity\SiteService;
use App\Services\Activity\SuppliesParameterService;
use App\Services\Activity\SuppliesService;
use App\Services\Activity\ThemeService;
use App\Services\Event\ActivityService;

class ActivityController extends ApiController
{
    public $activityService;
    public $themeService;
    public $siteService;
    public $suppliesService;
    public $suppliesParameterService;
    public $activityPrizeService;

    /**
     * ActivityController constructor.
     * @param ActivityService $activityService
     * @param ThemeService $themeService
     * @param SiteService $siteService
     * @param SuppliesService $suppliesService
     * @param SuppliesParameterService $suppliesParameterService
     * @param PrizeService $prizeService
     */
    public function __construct(ActivityService $activityService,
                                ThemeService $themeService,
                                SiteService $siteService,
                                SuppliesService $suppliesService,
                                SuppliesParameterService $suppliesParameterService,
                                PrizeService $prizeService)
    {
        parent::__construct();
        $this->activityService  = $activityService;
        $this->themeService     = $themeService;
        $this->siteService      = $siteService;
        $this->suppliesService  = $suppliesService;
        $this->suppliesParameterService  = $suppliesParameterService;
        $this->activityPrizeService  = $prizeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity",
     *     tags={"精选活动后台"},
     *     summary="添加活动",
     *     operationId="add_activity",
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
     *         name="name",
     *         in="query",
     *         description="活动名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="活动地点",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="活动价格（单位：元）",
     *         required=false,
     *         @OA\Schema(
     *             type="float"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="活动主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="活动开始时间（例如：2019-10-01 08:30）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="活动结束时间",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="site_id",
     *         in="query",
     *         description="场地ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="supplies_ids",
     *         in="query",
     *         description="相关用品id串（例如1,2,66,）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认0，1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="banner_ids",
     *         in="query",
     *         description="轮播图ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="详情图ID串（例如22,56,56）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="firm",
     *         in="query",
     *         description="参会单位（多个单位使用|隔开）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notice",
     *         in="query",
     *         description="参会须知",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="活动详情",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="活动状态（1、开启，2、关闭）",
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
    public function addActivity(){
        $rules = [
            'name'          => 'required',
            'address'       => 'required',
            'price'         => 'regex:/^\-?\d+(\.\d{1,2})?$/',
            'theme_id'      => 'required|integer',
            'start_time'    => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'end_time'      => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])\s([0-1][0-9]|2[0-4])[:][0-5][0-9]$/'
            ],
            'site_id'       => 'required|integer',
            'supplies_ids'  => 'regex:/^(\d+[,])*$/',
            'is_recommend'  => 'in:0,1',
            'banner_ids'    => 'required|regex:/^(\d+[,])*\d+$/',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'name.required'         => '活动名称不能为空',
            'address.required'      => '活动地点不能为空',
            'price.regex'           => '活动价格格式有误',
            'theme_id.required'     => '活动主题不能为空',
            'theme_id.integer'      => '活动主题必须为整数',
            'start_time.required'   => '活动开始时间不能为空',
            'start_time.regex'      => '活动开始时间格式有误，例如：2019-10-10 12:30',
            'end_time.required'     => '活动结束时间不能为空',
            'end_time.regex'        => '活动结束时间格式有误，例如：2019-10-10 12:30',
            'site_id.required'      => '活动场地不能为空',
            'site_id.integer'       => '活动场地ID必须为整数',
            'supplies_ids.regex'    => '活动用品ID串格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'banner_ids.required'   => '活动banner图不能为空',
            'banner_ids.regex'      => '活动banner图ID串格式有误',
            'image_ids.required'    => '活动详情图不能为空',
            'image_ids.regex'       => '活动详情图ID串格式有误',
            'status.required'       => '活动状态不能为空',
            'status.in'             => '活动状态取值不在范围内',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->activityService->addActivity($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->activityService->message];
        }
        return ['code' => 100, 'message' => $this->activityService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="添加活动主题",
     *     operationId="add_activity_theme",
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
     *         name="name",
     *         in="query",
     *         description="主题名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="主题说明",
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
    public function addActivityTheme(){
        $rules = [
            'name'          => 'required',
        ];
        $messages = [
            'name.required'         => '主题名称不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->addTheme($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="删除活动主题",
     *     operationId="delete_activity_theme",
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
     *         description="主题id",
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
    public function deleteActivityTheme(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '主题ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->deleteTheme($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="修改活动主题",
     *     operationId="edit_activity_theme",
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
     *         description="主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="主题名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="主题说明",
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
    public function editActivityTheme(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
        ];
        $messages = [
            'id.required'           => '主题ID不能为空',
            'id.integer'            => '主题ID必须为整数',
            'name.required'         => '主题名称不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->editTheme($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_theme_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动主题列表",
     *     operationId="activity_theme_list",
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
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function activityThemeList(){
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
        $res = $this->themeService->getThemeList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100, 'message' => $this->themeService->error];
        }
        return ['code' => 200, 'message' => $this->themeService->message,'data' => $res];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_site",
     *     tags={"精选活动后台"},
     *     summary="添加活动场地",
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
        $res = $this->siteService->getSiteList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100, 'message' => $this->siteService->error];
        }
        return ['code' => 200, 'message' => $this->siteService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="添加活动用品",
     *     operationId="add_activity_supplies",
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
     *         name="name",
     *         in="query",
     *         description="用品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="用品价格（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认0，1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="用品链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="用品详情",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="用品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="用品来源（1、自营，2、第三方）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="使用场景主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameter",
     *         in="query",
     *         description="用品参数，json格式数据（例如：{'品名':'吉祥如意','材料':'红檀木（非洲红酸枝）','适用':'自用、收藏、摆件、送礼','如意':'长31cm 高9.5cm 宽9cm'}）",
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
    public function addActivitySupplies(){
        $rules = [
            'name'          => 'required',
            'price'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'is_recommend'  => 'in:0,1',
            'link'          => 'url',
            'detail'        => 'required',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'source'        => 'required|in:1,2',
            'theme_id'      => 'required|integer',
            'parameter'     => 'json'
        ];
        $messages = [
            'name.required'         => '用品名称不能为空',
            'price.required'        => '用品价格不能为空',
            'price.regex'           => '用品价格格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'link.url'              => '用品链接不是一个url',
            'detail.required'       => '用品详情不能为空',
            'image_ids.required'    => '用品图片不能为空',
            'image_ids.regex'       => '用品图片ID串格式有误',
            'source.required'       => '用品来源不能为空',
            'source.in'             => '用品来源不存在',
            'theme_id.required'     => '主题不能为空',
            'theme_id.integer'      => '主题ID必须为整数',
            'parameter.json'        => '用品参数必须为json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->addSupplies($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="删除活动用品",
     *     operationId="delete_activity_supplies",
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
     *         description="用品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function deleteActivitySupplies(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'       => '用品ID不能为空',
            'id.integer'        => '用品ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->deleteSupplies($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_supplies",
     *     tags={"精选活动后台"},
     *     summary="修改活动用品",
     *     operationId="edit_activity_supplies",
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
     *         description="用品ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="用品名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="用品价格（单位：元）",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐(默认0，1推荐)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link",
     *         in="query",
     *         description="用品链接",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="用品详情",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="用品图片ID串",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="用品来源（1、自营，2、第三方）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="theme_id",
     *         in="query",
     *         description="使用场景主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parameter",
     *         in="query",
     *         description="用品参数，json格式数据（例如：'{'品名':'吉祥如意','材料':'红檀木（非洲红酸枝）','适用':'自用、收藏、摆件、送礼','如意':'长31cm 高9.5cm 宽9cm'}'）",
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
    public function editActivitySupplies(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'price'         => 'required:regex:/^\-?\d+(\.\d{1,2})?$/',
            'is_recommend'  => 'in:0,1',
            'link'          => 'url',
            'detail'        => 'required',
            'image_ids'     => 'required|regex:/^(\d+[,])*\d+$/',
            'source'        => 'required|in:1,2',
            'theme_id'      => 'required|integer',
            'parameter'     => 'json',
        ];
        $messages = [
            'id.required'           => '用品ID不能为空',
            'id.integer'            => '用品ID必须为整数',
            'name.required'         => '用品名称不能为空',
            'price.required'        => '用品价格不能为空',
            'price.regex'           => '用品价格格式有误',
            'is_recommend.in'       => '是否推荐取值不在范围内',
            'link.url'              => '用品链接不是一个url',
            'detail.required'       => '用品详情不能为空',
            'image_ids.required'    => '用品图片不能为空',
            'image_ids.regex'       => '用品图片ID串格式有误',
            'source.required'       => '用品来源不能为空',
            'source.in'             => '用品来源不存在',
            'theme_id.required'     => '主题不能为空',
            'theme_id.integer'      => '主题ID必须为整数',
            'parameter.json'        => '用品参数必须为json格式',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->suppliesService->editSupplies($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->suppliesService->message];
        }
        return ['code' => 100, 'message' => $this->suppliesService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_supplies_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动用品列表",
     *     operationId="activity_supplies_list",
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
    public function activitySuppliesList(){
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
        $res = $this->suppliesService->getSuppliesList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100, 'message' => $this->suppliesService->error];
        }
        return ['code' => 200, 'message' => $this->suppliesService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/activity/activity_add_prize",
     *     tags={"精选活动后台"},
     *     summary="活动添加奖品",
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
}