<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\OaMemberService;

class OaMemberController extends ApiController
{
    protected $OaMemberService;

    public function __construct(OaMemberService $OaMemberService)
    {
        parent::__construct();
        $this->OaMemberService = $OaMemberService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/member_list",
     *     tags={"OA成员管理"},
     *     summary="获取成员列表(OA)",
     *     operationId="member_list",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【会员卡号，成员中文名，成员英文名，成员手机号,】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="成员等级",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="成员类别",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="成员性别[1男 2女]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="时间排序【1正序 2倒叙】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_home_detail",
     *         in="query",
     *         description="是否在首页显示排序【0不显示 1显示】",
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
     *     @OA\Response(response=100,description="获取成员列表失败",),
     * )
     *
     */
    public function memberList()
    {
        $rules = [
            'page'            => 'integer',
            'page_num'        => 'integer',
            'grade'           => 'integer',
            'category'        => 'integer',
            'is_home_detail'  => 'in:0,1',
            'sex'             => 'in:1,2',
            'asc'             => 'in:1,2',
        ];
        $messages = [
            'sex.in'                   => '性别类型不存在',
            'asc.in'                   => '排序类型不存在',
            'is_home_detail.in'        => '显示类型不存在',
            'page.integer'             => '页码必须为整数',
            'page_num.integer'         => '每页显示条数必须为整数',
            'grade.integer'            => '成员等级不存在',
            'category.integer'         => '成员类别不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->memberList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member_base",
     *     tags={"OA成员管理"},
     *     summary="添加成员基本信息",
     *     operationId="add_member_base",
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
     *         name="card_no",
     *         in="query",
     *         description="成员卡号",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="ch_name",
     *         in="query",
     *         description="成员中文名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="en_name",
     *         in="query",
     *         description="成员英文名",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="成员手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="成员等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="成员性别【1男，2女】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="avatar_id",
     *         in="query",
     *         description="成员头像",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="成员邮箱",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="成员类别",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birthplace",
     *         in="query",
     *         description="籍贯",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="zipcode",
     *         in="query",
     *         description="成员邮编",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="成员身份【默认0成员、1官员】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="成员是否隐藏【0显示、默认1隐藏】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="成员地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="wechat_no",
     *         in="query",
     *         description="微信号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birthday",
     *         in="query",
     *         description="成员生日【2018-12-19】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="zodiac",
     *         in="query",
     *         description="成员生肖",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="constellation",
     *         in="query",
     *         description="成员星座",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_at",
     *         in="query",
     *         description="等级到期时间",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员基本信息失败",),
     * )
     *
     */
    public function addMemberBase()
    {
        $rules = [
            'card_no'         => 'required|integer|unique:member_base',
            'mobile'          => 'required|mobile',
            'email'           => 'email|unique:member_base',
            'ch_name'         => 'required',
            'sex'             => 'required|in:1,2',
            'avatar_id'       => 'required|integer',
            'id_card'         => 'regex:/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/|unique:member_base',
            'category'        => 'required|integer',
            'status'          => 'required|in:0,1',
            'hidden'          => 'required|in:0,1',
            'zipcode'         => 'regex:/\d{6}/',
            'grade'           => 'required|integer',
            'end_at'          => 'required|in:0,1,2,3,4',
        ];
        $messages = [
            'card_no.required'   => '成员卡号不能为空',
            'card_no.integer'    => '成员卡号不是整数',
            'card_no.unique'     => '成员卡号已存在',
            'mobile.required'    => '成员手机号码不能为空',
            'mobile.mobile'      => '成员手机号码格式不正确',
            'email.email'        => '成员邮箱格式不正确',
            'email.unique'       => '成员邮箱已存在',
            'sex.required'       => '成员性别不能为空',
            'sex.in'             => '性别不存在',
            'avatar_id.required' => '成员头像不能为空',
            'avatar_id.integer'  => '成员头像不是整数',
            'category.required'  => '成员类别不能为空',
            'category.integer'   => '成员类别不是整数',
            'status.required'    => '成员身份不能为空',
            'status.in'          => '成员身份不存在',
            'hidden.required'    => '成员是否隐藏不能为空',
            'hidden.in'          => '成员是否隐藏格式不存在',
            'zipcode.regex'      => '成员邮编格式不正确',
            'grade.required'     => '成员等级不能为空',
            'grade.integer'      => '成员等级格式不正确',
            'end_at.required'    => '成员等级结束时间不能为空',
            'end_at.in'          => '成员等级结束时间格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMemberBase($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member_info",
     *     tags={"OA成员管理"},
     *     summary="成员风采展示信息",
     *     operationId="add_member_info",
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
     *      @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="employer",
     *         in="query",
     *         description="工作单位名称",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="工作单位职务",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="社会职务（头衔）",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="industry",
     *         in="query",
     *         description="从事行业【IT，互联网】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="brands",
     *         in="query",
     *         description="品牌",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="run_wide",
     *         in="query",
     *         description="经营范围",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="good_at",
     *         in="query",
     *         description="个人擅长",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="degree",
     *         in="query",
     *         description="最高学历",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="school",
     *         in="query",
     *         description="毕业院校",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="referral_agency",
     *         in="query",
     *         description="推荐机构",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="info_provider",
     *         in="query",
     *         description="会员信息提供者",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="archive",
     *         in="query",
     *         description="是否有存档，默认0无，1有",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐【0不推荐 1推荐】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_home_detail",
     *         in="query",
     *         description="设置成员是否在首页显示【默认0不显示 1显示】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="remarks",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="profile",
     *         in="query",
     *         description="个人简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员简历信息失败",),
     * )
     *
     */
    public function addMemberInfo()
    {
        $rules = [
            'member_id'       => 'required|integer|unique:member_info',
            'archive'         => 'required|in:0,1',
            'is_recommend'    => 'required|in:0,1',
            'is_home_detail'  => 'required|in:0,1',
        ];
        $messages = [
            'member_id.required'        => '成员ID不能为空',
            'member_id.integer'         => '成员ID不是整数',
            'member_id.unique'          => '成员ID已存在',
            'archive.required'          => '成员是否有存档不能为空',
            'archive.in'                => '成员是否有存档类型不存在',
            'is_recommend.required'     => '成员是否推荐不能为空',
            'is_recommend.in'           => '成员是否推荐类型不存在',
            'is_home_detail.required'   => '成员是否首页推荐不能为空',
            'is_home_detail.in'         => '成员是否首页推荐类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMemberInfo($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member_service",
     *     tags={"OA成员管理"},
     *     summary="添加成员服务信息",
     *     operationId="add_member_service",
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
     *      @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="publicity",
     *         in="query",
     *         description="是否需要宣传，默认0不需要，1需要",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="protocol",
     *         in="query",
     *         description="是否签署天朝上品微代理协议，默认0否，1是",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="nameplate",
     *         in="query",
     *         description="铭牌状态，默认0未制作，1已制作未送，2已送",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="other_server",
     *         in="query",
     *         description="其他服务 [0需要 1不需要]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="attendant",
     *         in="query",
     *         description="服务经理",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="member_attendant",
     *         in="query",
     *         description="会籍服务人员",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="gift",
     *         in="query",
     *         description="伴手礼",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="成员偏好类型【1,2,3,4,5,6】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员服务信息失败",),
     * )
     *
     */
    public function addMemberService()
    {
        $rules = [
            'member_id'     => 'required|integer|unique:member_personal_service|unique:member_preference',
            'protocol'      => 'required|in:0,1',
            'nameplate'     => 'required|in:0,1,2',
            'other_server'  => 'required|in:0,1',
        ];
        $messages = [
            'member_id.required'    => '成员ID不能为空',
            'member_id.integer'     => '成员ID不是整数',
            'member_id.unique'      => '成员ID已存在',
            'protocol.required'     => '成员是否需要宣传不能为空',
            'protocol.in'           => '成员是否需要宣传类型不存在',
            'nameplate.required'    => '成员铭牌状态不能为空',
            'nameplate.in'          => '成员铭牌状态类型不存在',
            'other_server.required' => '成员其他服务不能为空',
            'other_server.in'       => '成员其他服务类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMemberService($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_member_base",
     *     tags={"OA成员管理"},
     *     summary="编辑成员基础展示信息",
     *     operationId="edit_member_base",
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
     *         description="成员id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="card_no",
     *         in="query",
     *         description="成员卡号",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="ch_name",
     *         in="query",
     *         description="成员中文名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="en_name",
     *         in="query",
     *         description="成员英文名",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="成员手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="成员等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="成员性别【1男，2女】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="avatar_id",
     *         in="query",
     *         description="成员头像",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="成员邮箱",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="成员类别",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birthplace",
     *         in="query",
     *         description="籍贯",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="zipcode",
     *         in="query",
     *         description="成员邮编",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="成员身份【默认0成员、1官员】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="成员是否隐藏【0显示、默认1隐藏】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="成员地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="wechat_no",
     *         in="query",
     *         description="微信号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birthday",
     *         in="query",
     *         description="成员生日【2018-12-19】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="zodiac",
     *         in="query",
     *         description="成员生肖",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="constellation",
     *         in="query",
     *         description="成员星座",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_at",
     *         in="query",
     *         description="等级截止时间",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员基本信息失败",),
     * )
     *
     */
    public function editMemberBase()
    {
        $rules = [
            'id'              => 'required|integer',
            'card_no'         => 'required|integer',
            'mobile'          => 'required|mobile',
            'email'           => 'email',
            'ch_name'         => 'required',
            'sex'             => 'required|in:1,2',
            'avatar_id'       => 'required|integer',
            'id_card'         => [
                'regex:/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/',
                'unique:member_base'
            ],
            'category'        => 'required|integer',
            'status'          => 'required|in:0,1',
            'hidden'          => 'required|in:0,1',
            'zipcode'         => 'regex:/^\d{6}$/',
            'grade'           => 'required|integer',
            'end_at'          => 'required',
            'birthday'        => 'date_format:"Y-m-d"',
        ];
        $messages = [
            'id.required'        => '成员ID不能为空',
            'id.integer'         => '成员ID不能为空',
            'card_no.required'   => '成员卡号不能为空',
            'card_no.integer'    => '成员卡号不是整数',
            'mobile.required'    => '成员手机号码不能为空',
            'mobile.mobile'      => '成员手机号码格式不正确',
            'email.email'        => '成员邮箱格式不正确',
            'sex.required'       => '成员性别不能为空',
            'sex.in'             => '性别不存在',
            'avatar_id.required' => '成员头像不能为空',
            'avatar_id.integer'  => '成员头像不是整数',
            'category.required'  => '成员类别不能为空',
            'category.integer'   => '成员类别不是整数',
            'status.required'    => '成员身份不能为空',
            'status.in'          => '成员身份不存在',
            'hidden.required'    => '成员是否隐藏不能为空',
            'hidden.in'          => '成员是否隐藏格式不存在',
            'zipcode.regex'      => '成员邮编格式不正确',
            'grade.required'     => '成员级别不能为空',
            'grade.integer'      => '成员级别格式不正确',
            'end_at.required'    => '成员等级截止时间不能为空',
            'birthday.date_format'     => '成员生日格式不正确'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->editMemberBase($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_member_info",
     *     tags={"OA成员管理"},
     *     summary="编辑成员风采展示信息",
     *     operationId="edit_member_info",
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
     *      @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="employer",
     *         in="query",
     *         description="工作单位名称",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="工作单位职务",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="社会职务（头衔）",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="industry",
     *         in="query",
     *         description="从事行业【IT，互联网】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="brands",
     *         in="query",
     *         description="品牌",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="run_wide",
     *         in="query",
     *         description="经营范围",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="good_at",
     *         in="query",
     *         description="个人擅长",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="degree",
     *         in="query",
     *         description="最高学历",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="school",
     *         in="query",
     *         description="毕业院校",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="referral_agency",
     *         in="query",
     *         description="推荐机构",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="info_provider",
     *         in="query",
     *         description="会员信息提供者",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="archive",
     *         in="query",
     *         description="是否有存档，默认0无，1有",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐【0不推荐 1推荐】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_home_detail",
     *         in="query",
     *         description="设置成员是否在首页显示【默认0不显示 1显示】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="remarks",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="profile",
     *         in="query",
     *         description="个人简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员简历信息失败",),
     * )
     *
     */
    public function editMemberInfo()
    {
        $rules = [
            'member_id'       => 'required|integer',
            'archive'         => 'in:0,1',
            'is_recommend'    => 'in:0,1',
            'is_home_detail'  => 'in:0,1',
        ];
        $messages = [
            'member_id.required'        => '成员ID不能为空',
            'member_id.integer'         => '成员ID不是整数',
            'archive.in'                => '成员是否有存档类型不存在',
            'is_recommend.in'           => '成员是否推荐类型不存在',
            'is_home_detail.in'         => '成员是否首页推荐类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->editMemberInfo($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_member_service",
     *     tags={"OA成员管理"},
     *     summary="编辑会员喜好需求信息展示",
     *     operationId="edit_member_service",
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
     *      @OA\Parameter(
     *         name="member_id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="publicity",
     *         in="query",
     *         description="是否需要宣传，默认0不需要，1需要",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="protocol",
     *         in="query",
     *         description="是否签署天朝上品微代理协议，默认0否，1是",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="nameplate",
     *         in="query",
     *         description="铭牌状态，默认0未制作，1已制作未送，2已送",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="other_server",
     *         in="query",
     *         description="其他服务 [0需要 1不需要]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="attendant",
     *         in="query",
     *         description="服务经理",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="member_attendant",
     *         in="query",
     *         description="会籍服务人员",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="gift",
     *         in="query",
     *         description="伴手礼",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="成员偏好类型【1,2,3,4,5,6】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加成员服务信息失败",),
     * )
     *
     */
    public function editMemberService()
    {
        $rules = [
            'member_id'     => 'required|integer',
            'protocol'      => 'in:0,1',
            'nameplate'     => 'in:0,1,2',
            'other_server'  => 'in:0,1',
        ];
        $messages = [
            'member_id.required'    => '成员ID不能为空',
            'member_id.integer'     => '成员ID不是整数',
            'protocol.in'           => '成员是否需要宣传类型不存在',
            'nameplate.in'          => '成员铭牌状态类型不存在',
            'other_server.in'       => '成员其他服务类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->editMemberService($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->OaMemberService->error];
        }
        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $res];
    }












    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_member_info",
     *     tags={"OA成员管理"},
     *     summary="获取成员信息",
     *     operationId="get_member_info",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="成员 ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getMemberInfo()
    {
        $rules = [
            'id'            => 'integer',
        ];
        $messages = [
            'id.integer'    => 'ID格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        $res = $this->OaMemberService->getMemberInfo($id);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => ['memberInfo' => $res]];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/del_member",
     *     tags={"OA成员管理"},
     *     summary="删除成员",
     *     operationId="del_member",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="成员 ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function delMember()
    {
        $rules = [
            'id'          => 'required|integer',
        ];
        $messages = [
            'id.required'             => '会员ID不能为空',
            'id.integer'              => 'ID格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->delMember($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];

    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/set_member_status",
     *     tags={"OA成员管理"},
     *     summary="成员 禁用or激活",
     *     operationId="set_member_status",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="成员 ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="显示or隐藏【0显示、1隐藏】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function setMemberStatus()
    {
        $rules = [
            'id'              => 'required|integer',
            'hidden'          => 'required|in:0,1',
        ];
        $messages = [
            'id.required'     => '成员ID不能为空',
            'id.integer'      => '成员ID不是整数',
            'hidden.required' => '状态属性不能为空',
            'hidden.in'       => '状态属性不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->setMemberStatus($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member_grade_view",
     *     tags={"OA成员管理"},
     *     summary="添加成员可查看会员权限",
     *     operationId="add_member_grade_view",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型 1等级 2成员身份",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="成员 等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         description="可查看成员等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function addMemberGradeView()
    {
        $rules = [
            'type'            => 'required|in:1,2',
            'grade'           => 'required|integer',
            'value'           => 'required|integer',
        ];
        $messages = [
            'type.required'   => '类型不能为空',
            'type.in'         => '该类型不存在',
            'grade.required'  => '等级不能为空',
            'grade.integer'   => '等级不是整数',
            'value.required'  => '查看值不能为空',
            'value.integer'   => '查看值不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMemberGradeView($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_member_grade_view",
     *     tags={"OA成员管理"},
     *     summary="修改成员可查看会员权限",
     *     operationId="edit_member_grade_view",
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
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型 1等级 2成员身份",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="成员 等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         description="可查看成员等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function editMemberGradeView()
    {
        $rules = [
            'id'              => 'required|integer',
            'type'            => 'required|in:1,2',
            'grade'           => 'required|integer',
            'value'           => 'required|integer',
        ];
        $messages = [
            'id.required'     => 'ID不能为空',
            'id.integer'      => 'ID不是整数',
            'type.required'   => '类型不能为空',
            'type.in'         => '该类型不存在',
            'grade.required'  => '等级不能为空',
            'grade.integer'   => '等级不是整数',
            'value.required'  => '查看值不能为空',
            'value.integer'   => '查看值不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->editMemberGradeView($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_member_grade_view_list",
     *     tags={"OA成员管理"},
     *     summary="获取成员可查看会员权限列表",
     *     operationId="get_member_grade_view_list",
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
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getMemberGradeViewList()
    {
        $rules = [
            'page'            => 'integer',
            'page_num'        => 'integer',
        ];
        $messages = [
            'page.integer'             => '页码必须为整数',
            'page_num.integer'         => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->getMemberGradeViewList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => $res];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/set_member_home_detail",
     *     tags={"OA成员管理"},
     *     summary="设置成员是否在首页显示",
     *     operationId="set_member_home_detail",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="exhibition",
     *         in="query",
     *         description="首页显示 默认0不显示 1显示",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function setMemberHomeDetail()
    {
        $rules = [
            'id'              => 'required|integer',
            'exhibition'      => 'required|in:0,1',
        ];
        $messages = [
            'id.required'          => '成员ID不能为空',
            'id.integer'           => '成员ID不是整数',
            'exhibition.required'  => '首页显示类型不能为空',
            'exhibition.in'        => '首页显示类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->setMemberHomeDetail($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
    }
}