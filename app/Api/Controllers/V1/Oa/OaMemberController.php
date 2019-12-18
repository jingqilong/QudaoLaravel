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
            'is_home_detail'  => 'in:0,1',
            'asc'             => 'in:1,2',
        ];
        $messages = [
            'asc.integer'              => '排序类型不存在',
            'is_home_detail.integer'   => '显示类型不存在',
            'page.integer'             => '页码必须为整数',
            'page_num.integer'         => '每页显示条数必须为整数',
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
     *     @OA\Parameter(name="card_no",in="query",description="会员卡号",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="ch_name",in="query",description="中文名",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="avatar_id",in="query",description="会员头像",required=false,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="en_name",in="query",description="英文名",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="sex",in="query",description="性别[0未设置1男2女]",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="mobile",in="query",description="手机号",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="category",in="query",description="成员类别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="grade",in="query",description="会员级别",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="employer",in="query",description="工作单位名称",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="position",in="query",description="职务",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="industry",in="query",description="从事行业",required=true,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="email",in="query",description="邮箱",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="address",in="query",description="地址",required=false,@OA\Schema(type="string",)),
     *     @OA\Parameter(name="end_at",in="query",description="有效期[1一年 2两年 3三年 4五年 5永久有效]",required=true,@OA\Schema(type="integer",)),
     *     @OA\Parameter(name="profile",in="query",description="个人简介",required=false,@OA\Schema(type="string",)),
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
    public function addMember()
    {
        $rules = [
            'card_no'            => 'required',
            'sex'                => 'required|in:0,1,2',
            'ch_name'            => 'required|string',
            'en_name'            => 'string',
            'grade'              => 'required',
            'category'           => 'required',
            'mobile'             => 'required|regex:/^1[3456789][0-9]{9}$/',
            'email'              => 'email',
            'end_at'             => 'required|in:1,2,3,4,5',
            'status'             => 'required|in:0,1',
            'hidden'             => 'required|in:0,1',
        ];
        $messages = [
            'card_no.required'   => '请填写会员卡号',
            'sex.required'       => '请填写性别',
            'sex.in'             => '请正确填写性别',
            'end_at.required'    => '请填写有效期',
            'end_at.integer'     => '请正确填写有效期类型',
            'mobile.required'    => '请填写手机号码',
            'mobile.regex'       => '手机号码格式不正确',
            'ch_name.required'   => '中文名不能为空',
            'email.email'        => '邮箱格式不正确',
            'category.required'  => '请填写成员分类',
            'grade.required'     => '请填写成员级别',
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
    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_member_service_view",
     *     tags={"OA成员管理"},
     *     summary="添加成员可查看会员记录",
     *     operationId="add_member_service_view",
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
    public function addMemberServiceView()
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
        $res = $this->OaMemberService->addMemberServiceView($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OaMemberService->error];
        }
        return ['code' => 200, 'message' => $this->OaMemberService->message];
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