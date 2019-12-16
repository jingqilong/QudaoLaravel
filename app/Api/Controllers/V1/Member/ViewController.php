<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Enums\MemberEnum;
use App\Services\Member\GradeViewService;
use Illuminate\Http\JsonResponse;

class ViewController extends ApiController
{
    protected $gradeViewService;

    /**
     * TestApiController constructor.
     * @param GradeViewService $gradeViewService
     */
    public function __construct(GradeViewService $gradeViewService)
    {
        parent::__construct();
        $this->gradeViewService = $gradeViewService;
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
        $res = $this->gradeViewService->addViewMember($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeViewService->error];
        }
        return ['code' => 200, 'message' => $this->gradeViewService->message];
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
        $res = $this->gradeViewService->addGradeView($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->gradeViewService->error];
        }
        return ['code' => 200, 'message' => $this->gradeViewService->message];
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
        $res = $this->gradeViewService->deleteViewMember($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeViewService->error];
        }
        return ['code' => 200, 'message' => $this->gradeViewService->message];
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
        $res = $this->gradeViewService->restoreViewMember($this->request['id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->gradeViewService->error];
        }
        return ['code' => 200, 'message' => $this->gradeViewService->message];
    }
}