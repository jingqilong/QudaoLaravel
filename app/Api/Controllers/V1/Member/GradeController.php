<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\GradeDefineService;
use App\Services\Member\GradeServiceService;
use Illuminate\Http\JsonResponse;

class GradeController extends ApiController
{
    public $gradeDefineService;
    protected $gradeServiceService;

    /**
     * GradeController constructor.
     * @param GradeDefineService $gradeDefineService
     * @param GradeServiceService $gradeServiceService
     */
    public function __construct(GradeDefineService $gradeDefineService, GradeServiceService $gradeServiceService)
    {
        parent::__construct();
        $this->gradeDefineService = $gradeDefineService;
        $this->gradeServiceService = $gradeServiceService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_grade",
     *     tags={"会员权限"},
     *     summary="添加等级",
     *     description="sang",
     *     operationId="add_grade",
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
     *         name="iden",
     *         in="query",
     *         description="等级，数字，与系统中的枚举对应",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="等级标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="等级说明",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，0、启用，1、关闭",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minimum_time",
     *         in="query",
     *         description="购买最低时长，单位：年",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="购买单价金额，每年/元，单位：元",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_buy",
     *         in="query",
     *         description="是否可购买，0不可购买，1可购买",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_id",
     *         in="query",
     *         description="卡片（图片）ID",
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
    public function addGrade(){
        $rules = [
            'iden'          => 'required|integer',
            'title'         => 'required|max:50',
            'description'   => 'max:200',
            'status'        => 'required|in:0,1',
            'minimum_time'  => 'required|integer|min:1',
            'amount'        => 'required|integer|min:0',
            'is_buy'        => 'required|in:0,1',
            'image_id'      => 'integer',
        ];
        $messages = [
            'iden.required'         => '请输入等级',
            'iden.integer'          => '等级必须为整数',
            'title.required'        => '请输入等级标题',
            'title.max'             => '等级标题不能超过50字',
            'description.max'       => '等级说明不能超过200字',
            'status.required'       => '请输入状态',
            'status.in'             => '状态不存在',
            'minimum_time.required' => '请输入购买最低时长',
            'minimum_time.integer'  => '购买最低时长必须为整数',
            'minimum_time.min'      => '购买最低时长不能低于1年',
            'amount.required'       => '请输入单价金额',
            'amount.integer'        => '单价金额只能是整数',
            'amount.min'            => '单价金额不能低于0元',
            'is_buy.required'       => '请选择是否可购买',
            'is_buy.in'             => '是否可购买取值不存在',
            'image_id.integer'      => '卡片ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeDefineService->addGrade($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeDefineService->error];
        }
        return ['code' => 200, 'message' => $this->gradeDefineService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/member/delete_grade",
     *     tags={"会员权限"},
     *     summary="删除等级",
     *     description="sang",
     *     operationId="delete_grade",
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
     *         description="等级记录ID",
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
    public function deleteGrade(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'           => '等级记录ID不能为空',
            'id.integer'            => '等级记录ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeDefineService->deleteGrade($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeDefineService->error];
        }
        return ['code' => 200, 'message' => $this->gradeDefineService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_grade",
     *     tags={"会员权限"},
     *     summary="编辑等级",
     *     description="sang",
     *     operationId="edit_grade",
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
     *         description="等级记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="iden",
     *         in="query",
     *         description="等级，数字，与系统中的枚举对应",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="等级标题",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="等级说明",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，0、启用，1、关闭",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minimum_time",
     *         in="query",
     *         description="购买最低时长，单位：年",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="购买单价金额，每年/元，单位：元",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_buy",
     *         in="query",
     *         description="是否可购买，0不可购买，1可购买",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_id",
     *         in="query",
     *         description="卡片（图片）ID",
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
    public function editGrade(){
        $rules = [
            'id'            => 'required|integer',
            'iden'          => 'integer',
            'title'         => 'max:50',
            'description'   => 'max:200',
            'status'        => 'in:0,1',
            'minimum_time'  => 'integer|min:1',
            'amount'        => 'integer|min:0',
            'is_buy'        => 'in:0,1',
            'image_id'      => 'integer',
        ];
        $messages = [
            'id.required'           => '等级记录ID不能为空',
            'id.integer'            => '等级记录ID必须为整数',
            'iden.integer'          => '等级必须为整数',
            'title.max'             => '等级标题不能超过50字',
            'description.max'       => '等级说明不能超过200字',
            'status.in'             => '状态不存在',
            'minimum_time.integer'  => '购买最低时长必须为整数',
            'minimum_time.min'      => '购买最低时长不能低于1年',
            'amount.integer'        => '单价金额只能是整数',
            'amount.min'            => '单价金额不能低于0元',
            'is_buy.in'             => '是否可购买取值不存在',
            'image_id.integer'      => '卡片ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeDefineService->editGrade($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeDefineService->error];
        }
        return ['code' => 200, 'message' => $this->gradeDefineService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_list",
     *     tags={"会员权限"},
     *     summary="获取等级列表",
     *     description="sang",
     *     operationId="get_grade_list",
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
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function getGradeList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeDefineService->getGradeList($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->gradeDefineService->error];
        }
        return ['code' => 200, 'message' => $this->gradeDefineService->message,'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/grade_add_service",
     *     tags={"会员权限"},
     *     summary="给等级添加服务",
     *     description="给等级添加服务,sang",
     *     operationId="grade_add_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1、关闭，2、启用",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="数量，填写服务的服务次数或数量，如没有数量限制，使用*",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cycle",
     *         in="query",
     *         description="服务周期，单位：天，【表示至少多少天才能享用一次服务】",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function gradeAddService()
    {
        $rules = [
            'grade'         => 'required',
            'service_id'    => 'required|integer',
            'status'        => 'required|in:1,2',
            'number'        => ['required'],
            'cycle'         => 'required|integer|min:0',
        ];
        $messages = [
            'grade.required'        => '请输入等级',
            'service_id.required'   => '请输入服务ID',
            'service_id.integer'    => '服务ID必须为整数',
            'status.required'       => '请输入状态',
            'status.in'             => '状态为1或2',
            'number.required'       => '请输入数量',
            'cycle.required'        => '请输入服务周期',
            'cycle.integer'         => '服务周期只能是整数',
            'cycle.min'             => '服务周期不能低于0天',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->gradeAddService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/member/grade_delete_service",
     *     tags={"会员权限"},
     *     summary="删除等级中的服务",
     *     description="删除等级中的服务,sang",
     *     operationId="grade_delete_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function gradeDeleteService()
    {
        $rules = [
            'id'    => 'required|integer',
        ];
        $messages = [
            'id.required'   => '请输入记录ID',
            'id.integer'    => '记录ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->gradeDeleteService($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/grade_edit_service",
     *     tags={"会员权限"},
     *     summary="修改等级服务",
     *     description="修改等级对应服务记录，sang",
     *     operationId="grade_edit_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="记录ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，1、关闭，2、启用",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="数量，填写服务的服务次数或数量，如没有数量限制，使用*",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cycle",
     *         in="query",
     *         description="服务周期，单位：天，【表示至少多少天才能享用一次服务】",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function gradeEditService()
    {
        $rules = [
            'id'            => 'required|integer',
            'service_id'    => 'required|integer',
            'status'        => 'required|in:1,2',
            'number'        => ['required'],
            'cycle'         => 'required|integer|min:0',
        ];
        $messages = [
            'id.required'           => '请输入记录ID',
            'id.integer'            => '记录ID必须为整数',
            'service_id.required'   => '请输入服务ID',
            'service_id.integer'    => '服务ID必须为整数',
            'status.required'       => '请输入状态',
            'status.in'             => '状态为1或2',
            'number.required'       => '请输入数量',
            'cycle.required'        => '请输入服务周期',
            'cycle.integer'         => '服务周期只能是整数',
            'cycle.min'             => '服务周期不能低于0天',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->gradeEditService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/grade_service_detail",
     *     tags={"会员权限"},
     *     summary="获取等级下的服务详情",
     *     description="sang",
     *     operationId="grade_service_detail",
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
     *         name="grade",
     *         in="query",
     *         description="等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function gradeServiceDetail()
    {
        $rules = [
            'grade'         => 'required',
        ];
        $messages = [
            'grade.required'        => '等级不能为空',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->gradeServiceDetail($this->request['grade']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_service",
     *     tags={"会员"},
     *     summary="获取等级下的服务详情",
     *     description="sang",
     *     operationId="get_grade_service",
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
     *         description="会员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="grade",
     *         in="query",
     *         description="等级",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getGradeService(){
        $rules = [
            'grade'         => 'required',
        ];
        $messages = [
            'grade.required'        => '等级不能为空',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->getGradeService($this->request['grade']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_grade_cart_list",
     *     tags={"会员"},
     *     summary="获取等级卡片列表",
     *     description="sang",
     *     operationId="get_grade_cart_list",
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
     *         description="会员 token",
     *         required=true,
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
    public function getGradeCartList(){
        $res = $this->gradeServiceService->getGradeCartList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message, 'data' => $res];
    }
}