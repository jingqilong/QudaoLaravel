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
     *         description="搜索内容【会员卡号，成员中文名，成员英文名，成员类别，成员手机号,成员级别】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
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
        $memberInfo = $this->OaMemberService->getMemberInfo($id);
        if (!$memberInfo){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message, 'data' => ['memberInfo' => $memberInfo]];
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
     * @OA\Get(
     *     path="/api/v1/oa/set_member_status",
     *     tags={"OA成员管理"},
     *     summary="禁用or激活成员and官员",
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
     *     summary="添加成员基本信息",
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
     *     @OA\Parameter(name="token",in="query", description="OA TOKEN",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_num",in="query",description="会员卡号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_cname",in="query",description="中文名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_ename",in="query",description="英文名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_phone",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_sex",in="query",description="性别 1先生 2女士",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="m_category",in="query",description="成员类别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_groupname",in="query",description="会员级别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_workunits",in="query",description="工作单位名称",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_position",in="query",description="职务",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_industry",in="query",description="从事行业",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_address",in="query",description="地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_notes",in="query",description="备注",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_indate",in="query",description="有效期",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="m_introduce",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),

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
            'm_num'                      => 'required|integer',
            'm_sex'                      => 'required|integer',
            'm_cname'                    => 'required|string',
            'm_groupname'                => 'required',
            'm_category'                 => 'required',
            'm_email'                    => 'email',
        ];
        $messages = [
            'm_num.integer'              => '会员卡号格式不正确',
            'm_num.required'             => '请填写会员卡号',
            'm_sex.required'             => '请填写性别',
            'm_sex.integer'              => '请正确填写性别',
            'm_cname.string'             => '请正确填写姓名',
            'm_ename.string'             => '请正确填写姓名格',
            'm_cname.required'           => '请填写中文姓名',
            'm_email.email'              => '邮箱格式不正确',
            'm_category.required'        => '请填写成员分类',
            'm_groupname.required'       => '请填写成员级别',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->addMember($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/upd_member",
     *     tags={"OA成员管理"},
     *     summary="更新完善 成员信息",
     *     operationId="upd_member",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(name="token",in="query", description="OA TOKEN",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="id",in="query", description="用户ID",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="card_no",in="query",description="会员卡号",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="ch_name",in="query",description="中文名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="en_name",in="query",description="英文名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="sex",in="query",description="性别",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="grade",in="query",description="会员级别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="employer",in="query",description="工作单位名称",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="position",in="query",description="职务",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="title",in="query",description="社会职务",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="industry",in="query",description="从事行业",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="category",in="query",description="成员类别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="profile",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="avatar_id",in="query",description="会员头像",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="mobile",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="birthday",in="query",description="生日",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="address",in="query",description="地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="end_at",in="query",description="有效期[1一年 2两年 3三年 4五年 5永久有效]",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="info_provider",in="query",description="会员信息提供者",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="status",in="query",description="状态(身份)，默认0成员、1官员",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="hidden",in="query",description="是否隐藏，默认0显示、1隐藏",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="other_server",in="query",description="其他服务 [0需要 默认1不需要]",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="is_recommend",in="query",description="是否推荐，默认0不推荐、1推荐",required=false,@OA\Schema(type="integer",)),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function updMember()
    {
        $rules = [
            'id'                 => 'required|integer',
            'card_no'            => 'required',
            'sex'                => 'required|integer',
            'ch_name'            => 'required|string',
            'en_name'            => 'string',
            'grade'              => 'required',
            'category'           => 'required',
            'email'              => 'email',
            'end_at'             => 'required|in:1,2,3,4,5',
            'status'             => 'required|in:0,1',
            'hidden'             => 'required|in:0,1',
        ];
        $messages = [
            'id.integer'         => '会员ID格式不正确',
            'id.required'        => '请填写会员ID',
            'card_no.required'   => '请填写会员卡号',
            'sex.required'       => '请填写性别',
            'sex.integer'        => '请正确填写性别',
            'end_at.required'    => '请填写有效期',
            'end_at.integer'     => '请正确填写有效期类型',
            'ch_name.required'   => '中文名不能为空',
            'email.email'        => '邮箱格式不正确',
            'category.required'  => '请填写成员分类',
            'grade.required'     => '请填写成员级别',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OaMemberService->updMemberInfo($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
       return ['code' => 200, 'message' => $this->OaMemberService->message];
    }
}