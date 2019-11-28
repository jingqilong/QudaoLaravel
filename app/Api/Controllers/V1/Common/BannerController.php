<?php


namespace App\Api\Controllers\V1\Common;

use App\Api\Controllers\ApiController;
use App\Services\Common\HomeBannersService;
use App\Services\Common\HomeService;
use App\Services\Common\SmsService;
use App\Services\Member\CollectService;
use App\Services\Member\MemberService;

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
     *         name="module",
     *         in="query",
     *         description="模块，默认1主首页，2商城首页...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="banner类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="show_time",
     *         in="query",
     *         description="展示时间，表示从什么时候开始展示,示例：2019-10-26",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="相关ID，例如：类别为精选活动，此值为活动ID",
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
     *     @OA\Response(
     *         response=200,
     *         description="添加成功！",
     *     ),
     * )
     *
     */
    public function addBanners(){
        $rules = [
            'module'        => 'required|in:1,2',
            'type'          => 'required|in:1,2,3,4,5,6',
            'show_time'     => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])$/'
            ],
            'related_id'    => 'integer',
            'image_id'      => 'required|integer',
            'url'           => 'url',
        ];
        $messages = [
            'module.required'   => '模块不能为空！',
            'module.in'         => '模块不存在',
            'type.required'     => 'banner类型不能为空！',
            'type.in'           => 'banner类型不存在',
            'show_time.required'=> '展示时间不能为空！',
            'show_time.regex'   => '展示时间格式有误，示例：2019-10-26',
            'related_id.integer'=> '相关ID必须为整数',
            'image_id.required' => 'banner图不能为空！',
            'image_id.integer'  => 'banner图ID必须为整数',
            'url.url'           => '相关链接必须是一个有效的url',
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
     *         name="module",
     *         in="query",
     *         description="模块，默认1主首页，2商城首页...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="banner类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="show_time",
     *         in="query",
     *         description="展示时间，表示从什么时候开始展示,示例：2019-10-26",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="相关ID，例如：类别为精选活动，此值为活动ID",
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
            'module'        => 'required|in:1,2',
            'type'          => 'required|in:1,2,3,4,5,6',
            'show_time'     => [
                'required',
                'regex:/^[1-9][0-9]{3}[-](0[1-9]|1[0-2])[-](0[1-9]|[12][0-9]|3[0-2])$/'
            ],
            'related_id'    => 'integer',
            'image_id'      => 'required|integer',
            'url'           => 'url',
        ];
        $messages = [
            'id.required'      => 'banner记录ID不能为空',
            'id.integer'       => 'banner记录ID必须为整数',
            'module.required'   => '模块不能为空！',
            'module.in'         => '模块不存在',
            'type.required'     => 'banner类型不能为空！',
            'type.in'           => 'banner类型不存在',
            'show_time.required'=> '展示时间不能为空！',
            'show_time.regex'   => '展示时间格式有误，示例：2019-10-26',
            'related_id.integer'=> '相关ID必须为整数',
            'image_id.required' => 'banner图不能为空！',
            'image_id.integer'  => 'banner图ID必须为整数',
            'url.url'           => '相关链接必须是一个有效的url',
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
     *         name="module",
     *         in="query",
     *         description="模块，默认1主首页，2商城首页...",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="banner类型，1广告、2精选活动、3成员风采、4珍品商城、5房产租售、6精选生活",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
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
            'module'        => 'in:1,2',
            'type'          => 'in:1,2,3,4,5,6',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'module.in'         => '模块不存在',
            'type.in'           => 'banner类型不存在',
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
}