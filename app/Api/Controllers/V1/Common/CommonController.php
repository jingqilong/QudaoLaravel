<?php


namespace App\Api\Controllers\V1\Common;

use App\Api\Controllers\ApiController;
use App\Services\Common\CommonServiceTermsService;
use App\Services\Common\HomeBannersService;
use App\Services\Common\HomeService;
use App\Services\Common\PvService;
use App\Services\Common\SmsService;
use App\Services\Member\CollectService;
use App\Services\Member\MemberService;

class CommonController extends ApiController
{
    public $smsService;
    public $memberService;
    public $homeService;
    public $homeBannersService;
    public $collectService;
    public $commonServiceTermsService;

    /**
     * QiNiuController constructor.
     * @param SmsService $smsService
     * @param MemberService $memberService
     * @param HomeService $homeService
     * @param HomeBannersService $homeBannersService
     * @param CollectService $collectService
     * @param CommonServiceTermsService $commonServiceTermsService
     */
    public function __construct(SmsService $smsService,
                                MemberService $memberService,
                                HomeService $homeService,
                                HomeBannersService $homeBannersService,
                                CollectService $collectService,
                                CommonServiceTermsService $commonServiceTermsService)
    {
        parent::__construct();
        $this->smsService       = $smsService;
        $this->memberService    = $memberService;
        $this->homeService      = $homeService;
        $this->homeBannersService      = $homeBannersService;
        $this->collectService      = $collectService;
        $this->commonServiceTermsService      = $commonServiceTermsService;
        if(request()->path() == 'api/v1/common/home'){
            if (isset($this->request['token'])){
                $this->middleware('member.jwt.auth');
            }
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/send_captcha",
     *     tags={"公共"},
     *     summary="发送短信验证码",
     *     description="sang" ,
     *     operationId="send_captcha",
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
     *         name="type",
     *         in="query",
     *         description="短信类型【0，默认类型1、会员模块登录,2、修改密码,3、成员短信注册，4、成员绑定手机号,....】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function sendCaptcha(){
        $rules = [
            'type'      => 'required|integer',
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            'type.required'     => '请输入短信类型',
            'type.integer'      => '短信类型必须为整数',
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->smsService->sendCode($this->request['mobile'], $this->request['type']);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/mobile_exists",
     *     tags={"公共"},
     *     summary="检测成员手机号是否注册",
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
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="查询成功！",
     *     ),
     * )
     *
     */
    public function mobileExists(){
        $rules = [
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->mobileExists($this->request['mobile']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->memberService->error];
        }
        return ['code' => 200, 'message' => $this->memberService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/home",
     *     tags={"公共"},
     *     summary="获取首页",
     *     description="sang" ,
     *     operationId="home",
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
     *         description="会员token（非必填）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function home(){
        $res = $this->homeService->getHome();
        if ($res === false){
            return ['code' => 100, 'message' => $this->homeService->error];
        }
        PvService::recordPV();//添加访问量
        return ['code' => 200, 'message' => $this->homeService->message,'data' => $res];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/common/is_collect",
     *     tags={"公共"},
     *     summary="收藏或取消收藏",
     *     description="sang" ,
     *     operationId="is_collect_activity",
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
     *         name="type",
     *         in="query",
     *         description="收藏类别，1活动，2商品，3房产，4精选生活..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="target_id",
     *         in="query",
     *         description="收藏目标ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function isCollect(){
        $rules = [
            'type'          => 'required|in:1,2,3,4',
            'target_id'     => 'required|integer',
        ];
        $messages = [
            'type.required'         => '收藏类别不能为空',
            'type.in'               => '收藏类别不存在',
            'target_id.required'    => '收藏目标不能为空',
            'target_id.integer'     => '收藏目标ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->isCollect($this->request['type'],$this->request['target_id']);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/common/collect_list",
     *     tags={"公共"},
     *     summary="收藏类别列表",
     *     description="jing" ,
     *     operationId="collect_list",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="收藏列表类别，1活动，2商品，3房产，4餐饮",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="分页页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="分页数量",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function collectList(){
        $rules = [
            'type'          => 'required|in:1,2,3,4',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'type.required'         => '收藏类别不能为空',
            'type.in'               => '收藏类别不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '页码数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->collectList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/common/common_list",
     *     tags={"公共"},
     *     summary="获取评论列表",
     *     description="jing" ,
     *     operationId="common_list",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="商品的ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="分页页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="分页数量",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function commonList(){
        $rules = [
            'id'            => 'required|integer',
            'type'          => 'required|in:1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'id.required'           => '评论ID不能为空',
            'id.integer'            => '商城类别不能为空',
            'type.required'         => '评论类别不能为空',
            'type.in'               => '评论类别不存在',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '页码数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->commonList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/common/comments_list",
     *     tags={"公共"},
     *     summary="oa 获取评论列表",
     *     description="jing" ,
     *     operationId="comments_list",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="评论列表类别[1商城 （目前只有商城）默认全部]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字【1，姓名 2手机号】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="分页页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="分页数量",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function commentsList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'id.required'           => '评论ID不能为空',
            'id.integer'            => '商城类别不能为空',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '页码数量必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->commentsList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/common/add_comment",
     *     tags={"公共"},
     *     summary="添加商品评论",
     *     description="jing" ,
     *     operationId="add_comment",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_related_id",
     *         in="query",
     *         description="商品订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="related_id",
     *         in="query",
     *         description="【商品订单ID,商品的ID】比如【1,2】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="评论内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="评论图",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function addComment(){
        $rules = [
            'order_related_id' => 'required|integer',
            'related_id'    => 'required',
            'content'       => 'required',
            'type'          => 'required|in:1',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'order_related_id.required' => '商品订单ID不能为空',
            'order_related_id.integer'  => '商品订单ID不是整数',
            'related_id.required' => '商品ID不能为空',
            'content.required'    => '评论内容不能为空',
            'type.required'       => '评论类别不能为空',
            'type.in'             => '评论类别不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->addComment($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'comment_id' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/set_comment_status",
     *     tags={"公共"},
     *     summary="设置评论状态",
     *     description="jing" ,
     *     operationId="set_comment_status",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="评论ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="评论状态【2、审核通过，3、审核未通过...】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function setCommentStatus(){
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'required|in:2,3',
        ];
        $messages = [
            'id.required'   => '评论ID不能为空',
            'id.integer'    => '评论ID不是整数',
            'status.in'     => '设置评论状态不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->setCommentStatus($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_comment_details",
     *     tags={"公共"},
     *     summary="获取评论详情",
     *     description="jing" ,
     *     operationId="get_comment_details",
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
     *         name="type",
     *         in="query",
     *         description="评论列表类别，1商城 （目前只有商城）..",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="评论ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="",
     *     ),
     * )
     *
     */
    public function getCommentDetails(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'   => '评论ID不能为空',
            'id.integer'    => '评论id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->collectService->getCommentDetails($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->collectService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->collectService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_contact",
     *     tags={"公共"},
     *     summary="获取管家联系方式",
     *     description="sang" ,
     *     operationId="get_contact",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getContact(){
        if (!$contact = config('common.contact')){
            return ['code' => 100,'message' => '获取失败！'];
        }
        return ['code' => 200,'message' => '获取成功！','data' => $contact];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_common_service_terms",
     *     tags={"公共"},
     *     summary="获取渠道平台服务条款",
     *     description="jing" ,
     *     operationId="get_common_service_terms",
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
     *         name="type",
     *         in="query",
     *         description="类型【1注册登录条款.....】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getCommonServiceTerms(){
        $rules = [
            'type'        => 'required|in:1',
        ];
        $messages = [
            'type.required'  => '服务条款类型不能为空',
            'type.in'        => '服务条款类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonServiceTermsService->getCommonServiceTerms($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->commonServiceTermsService->error];
        }
        return ['code' => 200,'message' => $this->commonServiceTermsService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/add_common_service_terms",
     *     tags={"公共"},
     *     summary="添加渠道平台服务条款",
     *     description="jing" ,
     *     operationId="add_common_service_terms",
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
     *         name="type",
     *         in="query",
     *         description="类型【1注册登录条款.....】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         description="服务条款内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
    public function addCommonServiceTerms(){
        $rules = [
            'type'        => 'required|in:1',
            'value'       => 'required',
        ];
        $messages = [
            'type.required'  => '服务条款类型不能为空',
            'type.in'        => '服务条款类型不存在',
            'value.required' => '服务条款类型内容不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonServiceTermsService->addCommonServiceTerms($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->commonServiceTermsService->error];
        }
        return ['code' => 200,'message' => $this->commonServiceTermsService->message,'data' => $res];
    }
}