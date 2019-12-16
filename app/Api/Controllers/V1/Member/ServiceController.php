<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Enums\MemberEnum;
use App\Services\Member\GradeServiceService;
use App\Services\Member\ServiceConsumeService;
use App\Services\Member\ServiceService;
use Illuminate\Http\JsonResponse;

class ServiceController extends ApiController
{
    protected $serviceService;
    protected $gradeServiceService;
    protected $serviceConsumeService;

    /**
     * TestApiController constructor.
     * @param ServiceService $serviceService
     * @param GradeServiceService $gradeServiceService
     * @param ServiceConsumeService $serviceConsumeService
     */
    public function __construct(ServiceService $serviceService,
                                GradeServiceService $gradeServiceService,
                                ServiceConsumeService $serviceConsumeService)
    {
        parent::__construct();
        $this->serviceService           = $serviceService;
        $this->gradeServiceService      = $gradeServiceService;
        $this->serviceConsumeService    = $serviceConsumeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_service",
     *     tags={"会员权限"},
     *     summary="添加服务",
     *     description="只添加服务种类,sang",
     *     operationId="add_service",
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
     *         name="parent_id",
     *         in="query",
     *         description="父级服务ID，不填为顶级目录",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="服务名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="服务介绍",
     *         required=true,
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function addService()
    {
        $rules = [
            'parent_id' => 'integer',
            'name'      => 'required|string',
            'desc'      => 'required|string',
        ];
        $messages = [
            'parent_id.integer' => '父级服务ID必须为整数',
            'name.required'     => '请输入服务名称',
            'name.string'       => '服务名称只能是字符串',
            'desc.required'     => '请输入服务说明',
            'desc.string'       => '服务说明只能是字符串',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->addService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/service_detail",
     *     tags={"会员权限"},
     *     summary="获取服务详情",
     *     description="获取服务详情,sang",
     *     operationId="service_detail",
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
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function serviceDetail()
    {
        $rules = [
            'service_id'      => 'required|integer',
        ];
        $messages = [
            'service_id.required' => '服务ID不能为空',
            'service_id.integer' => '服务ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->serviceDetail($this->request['service_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message, 'data' => $res];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/member/delete_service",
     *     tags={"会员权限"},
     *     summary="删除服务",
     *     description="只删除服务种类,sang",
     *     operationId="delete_service",
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
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function deleteService()
    {
        $rules = [
            'service_id'      => 'required|integer',
        ];
        $messages = [
            'service_id.required' => '服务ID不能为空',
            'service_id.integer' => '服务ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->deleteService($this->request['service_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_service",
     *     tags={"会员权限"},
     *     summary="修改服务",
     *     description="用于修改服务信息,sang",
     *     operationId="edit_service",
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
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="服务名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="服务介绍",
     *         required=true,
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function editService()
    {
        $rules = [
            'service_id'=> 'required|integer',
            'name'      => 'required|string',
            'desc'      => 'required|string',
        ];
        $messages = [
            'service_id.required'   => '服务ID不能为空',
            'service_id.integer'    => '服务ID必须为整数',
            'name.required'         => '请输入服务名称',
            'name.string'           => '服务名称只能是字符串',
            'desc.required'         => '请输入服务说明',
            'desc.string'           => '服务说明只能是字符串',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->editService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/service_list",
     *     tags={"会员权限"},
     *     summary="获取服务列表",
     *     description="获取所有的服务列表,sang",
     *     operationId="service_list",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function serviceList()
    {
        $res = $this->serviceService->serviceList();
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message, 'data' => $res];
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
            'number'        => ['required','regex:/^([0-9]+|["*"])$/'],
            'cycle'         => 'required|integer|min:0',
        ];
        $messages = [
            'grade.required'        => '请输入等级',
            'service_id.required'   => '请输入服务ID',
            'service_id.integer'    => '服务ID必须为整数',
            'status.required'       => '请输入状态',
            'status.in'             => '状态为1或2',
            'number.required'       => '请输入数量',
            'number.regex'          => '数量格式有误',
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
     *     description="修改等级对应服务记录",
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
            'number'        => ['required','regex:/^([0-9]+|["*"])$/'],
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
            'number.regex'          => '数量格式有误',
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
     *     description="获取等级下的服务详情",
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
    public function gradeServiceDetail()
    {
        $rules = [
            'grade'         => 'required|in:1,2,3,4,5,6,7',
        ];
        $messages = [
            'grade.required'        => '请输入等级',
            'grade.in'              => '等级不存在',
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
     * @OA\Post(
     *     path="/api/v1/member/add_view_member",
     *     tags={"会员权限"},
     *     summary="添加会员可查看成员",
     *     description="添加会员可查看成员,sang",
     *     operationId="add_view_member",
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
     *         name="member_id",
     *         in="query",
     *         description="成员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="view_user_id",
     *         in="query",
     *         description="可查看成员ID【优先】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="view_user",
     *         in="query",
     *         description="可查看成员会员卡号、手机号、邮箱",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="添加成功！",
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败！",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function addViewMember()
    {
        $rules = [
            'member_id'         => 'required|integer',
            'view_user_id'      => 'integer',
        ];
        $messages = [
            'member_id.required'        => '成员ID不能为空',
            'member_id.integer'         => '成员ID必须为整数',
            'view_user_id.integer'      => '可查看成员ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        if (!isset($this->request['view_user_id']) && !isset($this->request['view_user'])){
            return ['code' => 100, 'message' => '请至少输入一个可查看成员条件'];
        }
        $res = $this->gradeServiceService->addViewMember($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }

 /**
     * @OA\Post(
     *     path="/api/v1/member/add_grade_view",
     *     tags={"会员权限"},
     *     summary="添加等级可查看成员",
     *     description="添加等级可查看成员,jing",
     *     operationId="add_grade_view",
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
     *         name="value",
     *         in="query",
     *         description="查看值[1 内部测试2 亦享成员3  至享成员4  悦享成员5  真享成员6  君享成员7  尊享成员8  致享成员9 高级顾问10 临时成员]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="添加成功！",
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败！",
     *     ),
     * )
     *
     */
    public function addGradeView()
    {
        $rules = [
            'grade'        => 'required|integer',
            'type'         => 'required|integer',
        ];
        $messages = [
            'grade.required'       => '等级不能为空',
            'grade.integer'        => '等级必须为整数',
            'type.required'        => '类型不能为空',
            'type.integer'         => '类型必须为整数',
            'value.required'       => '查看值不能为空',
            'value.in'             => '查看值不存在',
        ];
        if (1 == $this->request['type']){
            $rules['value'] = 'required|in:'.implode(',',array_keys(MemberEnum::$grade));
        }
        if (2 == $this->request['type']){
            $rules['value'] = 'required|in:'.implode(',',array_keys(MemberEnum::$identity));
        }

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->gradeServiceService->addGradeView($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/member/delete_view_member",
     *     tags={"会员权限"},
     *     summary="软删除会员可查看成员",
     *     description="软删除会员可查看成员,sang",
     *     operationId="delete_view_member",
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
    public function deleteViewMember()
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
        $res = $this->gradeServiceService->deleteViewMember($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/restore_view_member",
     *     tags={"会员权限"},
     *     summary="恢复会员可查看成员",
     *     description="恢复会员可查看成员,sang",
     *     operationId="restore_view_member",
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
    public function restoreViewMember()
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
        $res = $this->gradeServiceService->restoreViewMember($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeServiceService->error];
        }
        return ['code' => 200, 'message' => $this->gradeServiceService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/add_service_record",
     *     tags={"会员权限"},
     *     summary="添加会员服务消费记录",
     *     description="sang",
     *     operationId="add_service_record",
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
     *         name="member_id",
     *         in="query",
     *         description="会员ID",
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
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="消费数量",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="remark",
     *         in="query",
     *         description="备注",
     *         required=false,
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
    public function addServiceRecord(){
        $rules = [
            'member_id'     => 'required|integer',
            'service_id'    => 'required|integer',
            'number'        => 'required|integer',
            'remark'        => 'max:500',
        ];
        $messages = [
            'member_id.required'    => '会员ID不能为空',
            'member_id.integer'     => '会员ID必须为整数',
            'service_id.required'   => '服务ID不能为空',
            'service_id.integer'    => '服务ID必须为整数',
            'number.required'       => '服务次数不能为空',
            'number.string'         => '服务次数必须为整数',
            'remark.required'       => '备注字数不能超过500字',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceConsumeService->addServiceRecord($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceConsumeService->error];
        }
        return ['code' => 200, 'message' => $this->serviceConsumeService->message];
    }
}