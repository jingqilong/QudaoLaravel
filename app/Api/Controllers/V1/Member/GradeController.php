<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\GradeDefineService;

class GradeController extends ApiController
{
    public $gradeDefineService;

    /**
     * GradeController constructor.
     * @param $gradeDefineService
     */
    public function __construct(GradeDefineService $gradeDefineService)
    {
        parent::__construct();
        $this->gradeDefineService = $gradeDefineService;
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
}