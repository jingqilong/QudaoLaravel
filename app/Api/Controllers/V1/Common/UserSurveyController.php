<?php


namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Enums\UserSurveyHearFromEnum;
use App\Services\Common\CommonUserSurveyService;

class UserSurveyController extends ApiController
{
    public $commonUserSurveyService;

    /**
     * UserSurveyController constructor.
     * @param $commonUserSurveyService
     */
    public function __construct(CommonUserSurveyService $commonUserSurveyService)
    {
        parent::__construct();
        $this->commonUserSurveyService = $commonUserSurveyService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/user_survey/submit",
     *     tags={"用户调研"},
     *     summary="提交用户调研",
     *     description="sang" ,
     *     operationId="user_survey/submit",
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
     *         name="name",
     *         in="query",
     *         description="姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="性别，1先生，2女士",
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
     *     @OA\Parameter(
     *         name="hear_from",
     *         in="query",
     *         description="获知渠道",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         description="需求",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="提交失败",
     *     ),
     * )
     *
     */
    public function submitUserSurvey(){
        $rules = [
            'name'      => 'required|max:64',
            'gender'    => 'required|in:1,2',
            'mobile'    => 'required|mobile',
            'hear_from' => 'required|in:'.UserSurveyHearFromEnum::getCheckString(),
            'request'   => 'required|max:512',
        ];
        $messages = [
            'name.required'     => '请填写姓名!',
            'name.max'          => '姓名长度不能超过21个字!',
            'gender.required'   => '请选择性别!',
            'gender.in'         => '性别选择有误!',
            'mobile.required'   => '请填写手机号!',
            'mobile.mobile'     => '手机号格式有误!',
            'hear_from.required'=> '请选择获知渠道!',
            'hear_from.in'      => '获知渠道不存在！',
            'request.required'  => '请填写您的需求!',
            'request.max'       => '需求长度不能超过170个字!',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonUserSurveyService->submitUserSurvey($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonUserSurveyService->error];
        }
        return ['code' => 200, 'message' => $this->commonUserSurveyService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/common/user_survey/get_hear_from_list",
     *     tags={"用户调研"},
     *     summary="获取获知渠道",
     *     description="sang" ,
     *     operationId="user_survey/get_hear_from_list",
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
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getHearFromList(){
        $res = $this->commonUserSurveyService->getHearFromList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonUserSurveyService->error];
        }
        return ['code' => 200, 'message' => $this->commonUserSurveyService->message,'data' => $res];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/common/user_survey/delete",
     *     tags={"OA用户调研"},
     *     summary="删除用户调研",
     *     description="sang" ,
     *     operationId="user_survey/delete",
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
     *         description="ID",
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
    public function deleteUserSurvey(){
        $rules = [
            'id'      => 'required|integer',
        ];
        $messages = [
            'id.required'   => 'ID不能为空!',
            'id.integer'    => 'ID必须为整数!',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonUserSurveyService->deleteUserSurvey($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonUserSurveyService->error];
        }
        return ['code' => 200, 'message' => $this->commonUserSurveyService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/user_survey/set_status",
     *     tags={"OA用户调研"},
     *     summary="设置用户调研记录状态",
     *     description="sang" ,
     *     operationId="user_survey/set_status",
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
     *         description="ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function setStatus(){
        $rules = [
            'id'        => 'required|integer',
//            'status'    => 'required|integer',
        ];
        $messages = [
            'id.required'       => 'ID不能为空!',
            'id.integer'        => 'ID必须为整数!',
            'status.required'   => '状态不能为空!',
            'status.integer'    => '状态必须为整数!',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonUserSurveyService->setStatus($this->request['id'],$this->request['status']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->commonUserSurveyService->error];
        }
        return ['code' => 200, 'message' => $this->commonUserSurveyService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/user_survey/get_user_survey_list",
     *     tags={"OA用户调研"},
     *     summary="获取用户调研列表",
     *     description="sang" ,
     *     operationId="user_survey/get_user_survey_list",
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
     *         name="keywords",
     *         in="query",
     *         description="搜索，【姓名，手机号，需求】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hear_from",
     *         in="query",
     *         description="获知渠道",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getUserSurveyList(){
        $rules = [
            'hear_from'         => 'in:'.UserSurveyHearFromEnum::getCheckString(),
//            'status'            => 'in:',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'hear_from.in'          => '获知渠道不存在！',
            'status.in'             => '状态不存在！',
            'page.integer'          => '页码必须为整数！',
            'page_num.integer'      => '每页显示条数必须为整数！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->commonUserSurveyService->getUserSurveyList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->commonUserSurveyService->error];
        }
        return ['code' => 200, 'message' => $this->commonUserSurveyService->message,'data' => $res];
    }
}