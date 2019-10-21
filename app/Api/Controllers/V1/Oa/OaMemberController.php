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
     * @OA\Post(
     *     path="/api/v1/oa/get_member_list",
     *     tags={"OA成员管理"},
     *     summary="获取成员列表(OA)",
     *     operationId="get_member_list",
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
     *         description="搜索内容【会员卡号，成员中文名，成员英文名，成员类别，成员手机号,成员级别】",
     *         required=false,
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
     *             type="Integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="Integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取成员列表失败",),
     * )
     *
     */
    public function getMemberList()
    {
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

        $list = $this->OaMemberService->getMemberList($this->request);

        return ['code' => 200,'message' => $this->OaMemberService->message,'data' => $list];
    }

    /**
     * @OA\Post(
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
     *         description="用户TOKEN",
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
            'id'          => 'integer',
        ];
        $messages = [
            'page.integer'              => 'ID格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        if ($memberInfo = $this->OaMemberService->getMemberInfo($id)){
            return ['code' => 200, 'message' => '用户信息获取成功！', 'data' => ['memberInfo' => $memberInfo]];
        }
        return ['code' => 100, 'message' => '用户信息获取失败！'];
    }

    /**
     * @OA\Post(
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
     *         description="用户TOKEN",
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
            'id'          => 'integer',
        ];
        $messages = [
            'page.integer'              => 'ID格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        if ($member = $this->OaMemberService->delMember($id)){
            return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => ['member' => $member]];
        }
        return ['code' => 100, 'message' => $this->OaMemberService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/set_member_status",
     *     tags={"OA成员管理"},
     *     summary="禁用or激活成员",
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
     *         description="用户TOKEN",
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
    public function setMemberStatus()
    {
        $rules = [
            'id'          => 'integer',
        ];
        $messages = [
            'page.integer'              => 'ID格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        if ($member = $this->OaMemberService->setMemberStatus($id)){
            return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => ['member' => $member]];
        }
        return ['code' => 100, 'message' => $this->OaMemberService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member",
     *     tags={"OA成员管理"},
     *     summary="添加成员信息",
     *     operationId="add_member",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="token",in="query", description="用户TOKEN",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_num",in="query",description="会员卡号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_cname",in="query",description="中文名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_ename",in="query",description="英文名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_sex",in="query",description="性别",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_groupname",in="query",description="会员级别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_workunits",in="query",description="工作单位名称",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_position",in="query",description="职务",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_socialposition",in="query",description="社会职务",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_industry",in="query",description="从事行业",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_category",in="query",description="成员类别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_introduce",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_img",in="query",description="照片",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_phone",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_birthday",in="query",description="生日",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_numcard",in="query",description="身份证",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_address",in="query",description="地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_openid",in="query",description="微信openID",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_fixedphone",in="query",description="固定电话",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_idcard",in="query",description="身份证号",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_zipcode",in="query",description="邮编",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_works",in="query",description="工作类",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_socials",in="query",description="社交类",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_lifes",in="query",description="生活类",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_arts",in="query",description="艺术类",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_intacts",in="query",description="感兴趣活动",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_intactbest",in="query",description="最喜爱活动偏好",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_goodat",in="query",description="个人擅长",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_degree",in="query",description="最高学历",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_school",in="query",description="毕业院校",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_constellation",in="query",description="星座",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_zodiac",in="query",description="属相",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_birthplace",in="query",description="籍贯",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_notes",in="query",description="备注",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_indate",in="query",description="有效期",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_referrerid",in="query",description="推荐人卡号",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_referrername",in="query",description="推荐人姓名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_integrals",in="query",description="积分",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_storefront",in="query",description="登记店面",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_pushid",in="query",description="提成人编号",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_pushname",in="query",description="提成人姓名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_connection",in="query",description="高端人脉",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_doctors",in="query",description="名医特约",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_airport",in="query",description="机场特享",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_finance",in="query",description="金融支持",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_private",in="query",description="私人订制",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_magazine",in="query",description="杂志专访",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_wechatshow",in="query",description="微信朋友圈展示",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_wechattext",in="query",description="微信推广软文",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_actname",in="query",description="精彩活动名称",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_tcspwdl",in="query",description="是否签署天朝上品微代理协议",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_brandstrat",in="query",description="铭牌状态",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_pamanager",in="query",description="服务经理",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_memberships",in="query",description="会籍服务人",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_brands",in="query",description="品牌",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_business",in="query",description="经营范围",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_recby",in="query",description="推荐机构",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_zipaddress",in="query",description="杂志寄送地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_tablemer",in="query",description="会员信息表",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_bcardid",in="query",description="名片",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_gifthand",in="query",description="伴手礼",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_infop",in="query",description="个人资料提供者",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_starte",in="query",description="状态",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_wechatid",in="query",description="微信号",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_demand",in="query",description="需求",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_stored",in="query",description="总储值",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_opened_people",in="query",description="开卡人",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_major",in="query",description="专业",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_recognize",in="query",description="积分身份识别",required=false,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function addMember()
    {
        $rules = [
            'm_num'                             => 'required|integer',
            'm_sex'                             => 'required|integer',
            'm_cname'                           => 'required|string',
            'm_groupname'                       => 'required',
            'm_category'                        => 'required',
            'm_email'                           => 'email',
        ];
        $messages = [
            'm_num.integer'              => '会员卡号格式不正确',
            'm_num.required'             => '请填写会员卡号',
            'm_sex.required'             => '请填写性别',
            'm_sex.integer'              => '请正确填写性别',
            'm_cname.string'             => '请正确填写姓名',
            'm_ename.string'             => '请正确填写姓名格',
            'm_cname.required'           => '请填写中文姓名',
            'm_email.email'              => '请填写邮箱',
            'm_category.required'        => '请填写成员分类',
            'm_groupname.required'       => '请填写成员级别',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMember($this->request);
        if (!$res){
            return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => ['res' => $res]];
        }
        return ['code' => 100, 'message' => $this->OaMemberService->error];
    }
}