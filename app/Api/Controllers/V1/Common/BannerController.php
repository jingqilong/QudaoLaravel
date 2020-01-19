<?php


namespace App\Api\Controllers\V1\Common;

use App\Api\Controllers\ApiController;
use App\Services\Common\HomeBannersService;

class BannerController extends ApiController
{
    public $homeBannersService;

    /**
     * QiNiuController constructor.
     * @param HomeBannersService $homeBannersService
     */
    public function __construct(HomeBannersService $homeBannersService)
    {
        parent::__construct();
        $this->homeBannersService      = $homeBannersService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/add_home_banner",
     *     tags={"首页配置"},
     *     summary="添加首页banner",
     *     description="sang" ,
     *     operationId="mobile_exists",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_space",
     *         in="query",
     *         description="显示位置，默认1主首页，2商城首页...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link_type",
     *         in="query",
     *         description="链接类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="展示顺序，比如【第一张图、第二张图、第三张图、第四张图这四个位置】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="链接目标，例如：类别为精选活动，此值为活动ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_id",
     *         in="query",
     *         description="banner图ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="相关链接，例如：类别为广告时，此值为广告跳转的地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="展示状态，1展示，2隐藏",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="添加成功！",
     *     ),
     * )
     *
     */
    public function addBanners(){
        $rules = [
            'page_space'    => 'required|in:1,2',
            'link_type'     => 'required|in:1,2,3,4,5,6',
            'sort'          => 'required|in:1,2,3,4',
            'related_id'    => 'integer',
            'image_id'      => 'required|integer',
            'url'           => 'url',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'page_space.required'   => '显示位置不能为空！',
            'page_space.in'         => '显示位置不存在',
            'link_type.required'    => '链接类型不能为空！',
            'link_type.in'          => '链接类型不存在',
            'sort.required'         => '展示顺序不能为空！',
            'sort.in'               => '展示顺序不存在',
            'related_id.integer'    => '链接目标必须为整数',
            'image_id.required'     => 'banner图不能为空！',
            'image_id.integer'      => 'banner图ID必须为整数',
            'url.url'               => '相关链接必须是一个有效的url',
            'status.required'       => '展示状态不能为空',
            'status.in'             => '展示状态取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->homeBannersService->addBanners($this->request);
        if (!$res){
            return ['code' => 100,'message' => $this->homeBannersService->error];
        }
        return ['code' => 200, 'message' => $this->homeBannersService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/common/delete_banner",
     *     tags={"首页配置"},
     *     summary="删除banner图",
     *     operationId="delete_banner",
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
     *         description="banner记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteBanner(){
        $rules = [
            'id'           => 'required|integer'
        ];
        $messages = [
            'id.required'      => 'banner记录ID不能为空',
            'id.integer'       => 'banner记录ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->homeBannersService->deleteBanner($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->homeBannersService->error];
        }
        return ['code' => 200, 'message' => $this->homeBannersService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/edit_banners",
     *     tags={"首页配置"},
     *     summary="修改首页banner",
     *     description="sang" ,
     *     operationId="edit_banners",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="banner记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_space",
     *         in="query",
     *         description="显示位置，默认1主首页，2商城首页...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link_type",
     *         in="query",
     *         description="链接类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="展示顺序，比如【第一张图、第二张图、第三张图、第四张图这四个位置】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="链接目标，例如：类别为精选活动，此值为活动ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_id",
     *         in="query",
     *         description="banner图ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="相关链接，例如：类别为广告时，此值为广告跳转的地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="展示状态，1展示，2隐藏",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="修改成功！",
     *     ),
     * )
     *
     */
    public function editBanners(){
        $rules = [
            'id'            => 'required|integer',
            'page_space'    => 'required|in:1,2',
            'link_type'     => 'required|in:1,2,3,4,5,6',
            'sort'          => 'required|in:1,2,3,4',
            'related_id'    => 'integer',
            'image_id'      => 'required|integer',
            'url'           => 'url',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'id.required'      => 'banner记录ID不能为空',
            'id.integer'       => 'banner记录ID必须为整数',
            'page_space.required'   => '显示位置不能为空！',
            'page_space.in'         => '显示位置不存在',
            'link_type.required'    => '链接类型不能为空！',
            'link_type.in'          => '链接类型不存在',
            'sort.required'         => '展示顺序不能为空！',
            'sort.in'               => '展示顺序不存在',
            'related_id.integer'    => '链接目标必须为整数',
            'image_id.required'     => 'banner图不能为空！',
            'image_id.integer'      => 'banner图ID必须为整数',
            'url.url'               => '相关链接必须是一个有效的url',
            'status.required'       => '展示状态不能为空',
            'status.in'             => '展示状态取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->homeBannersService->editBanner($this->request);
        if (!$res){
            return ['code' => 100,'message' => $this->homeBannersService->error];
        }
        return ['code' => 200, 'message' => $this->homeBannersService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_banner_list",
     *     tags={"首页配置"},
     *     summary="获取首页banner图列表",
     *     operationId="get_banner_list",
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
     *         name="page_space",
     *         in="query",
     *         description="显示位置，默认1主首页，2商城首页...",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="link_type",
     *         in="query",
     *         description="链接类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="展示状态，1展示，2隐藏",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
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
    public function getBannerList(){
        $rules = [
            'page_space'    => 'in:1,2',
            'link_type'     => 'in:1,2,3,4,5,6',
            'status'        => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page_space.in'     => '显示位置不存在',
            'link_type.in'      => '链接类型不存在',
            'status.in'         => '展示状态取值有误',
            'page.integer'      => '页码必须为整数',
            'page_num.integer'  => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->homeBannersService->getBannerList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->homeBannersService->error];
        }
        return ['code' => 200, 'message' => $this->homeBannersService->message,'data' => $res];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/common/banner_status_switch",
     *     tags={"首页配置"},
     *     summary="banner显示状态开关",
     *     operationId="banner_status_switch",
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
     *         description="banner记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="展示状态，1展示，2隐藏",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function bannerStatusSwitch(){
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'id.required'       => 'banner记录ID不能为空',
            'id.integer'        => 'banner记录ID必须为整数',
            'status.required'   => '展示状态不能为空',
            'status.in'         => '展示状态取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->homeBannersService->bannerStatusSwitch($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->homeBannersService->error];
        }
        return ['code' => 200, 'message' => $this->homeBannersService->message];
    }
}