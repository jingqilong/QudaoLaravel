<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\PreferenceService;

class MemberPreferenceController extends ApiController
{
    public $MemberPreferenceService;

    /**
     * MemberPreferenceController constructor.
     * @param $MemberPreferenceService
     */
    public function __construct(PreferenceService $MemberPreferenceService)
    {
        parent::__construct();
        $this->MemberPreferenceService = $MemberPreferenceService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_preference_type",
     *     tags={"OA成员类别管理"},
     *     summary="添加成员活动偏好类别",
     *     operationId="add_preference_type",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="类别名称",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="content[类别介绍]",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function addPreferenceType(){
        $rules = [
            'name'     => 'required',
        ];
        $messages = [
            'name.required'  => '请填写类别名称',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->addPreferenceType($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/del_preference_type",
     *     tags={"OA成员类别管理"},
     *     summary="删除成员活动偏好类别",
     *     operationId="del_preference_type",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类别id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function delPreferenceType(){
        $rules = [
            'id'     => 'required|integer',
        ];
        $messages = [
            'id.required'  => '类别ID不能为空',
            'id.integer'   => '类别ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->delPreferenceType($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_preference_type",
     *     tags={"OA成员类别管理"},
     *     summary="修改成员活动偏好类别",
     *     operationId="edit_preference_type",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类别id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="类别名称",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="content[类别介绍]",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function editPreferenceType(){
        $rules = [
            'id'     => 'required|integer',
            'name'   => 'required',
        ];
        $messages = [
            'name.required'=> '请填写类别名称',
            'id.required'  => '类别ID不能为空',
            'id.integer'   => '类别ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->editPreferenceType($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_preference_info",
     *     tags={"OA成员类别管理"},
     *     summary="根据ID 获取成员类别信息",
     *     operationId="get_preference_info",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类别id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getPreferenceInfo(){
        $rules = [
            'id'     => 'required|integer',
        ];
        $messages = [
            'id.required'  => '类别ID不能为空',
            'id.integer'   => '类别ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->getPreferenceInfo($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_preference_list",
     *     tags={"OA成员类别管理"},
     *     summary="获取成员类别列表",
     *     operationId="get_preference_list",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getPreferenceList(){
        $res = $this->MemberPreferenceService->getPreferenceList($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/add_preference_value",
     *     tags={"OA成员类别管理"},
     *     summary="成员类别属性添加",
     *     operationId="add_preference_value",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="type",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="name",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="content",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function addPreferenceValue(){
        $rules = [
            'name'     => 'required',
            'type'     => 'required|integer',
        ];
        $messages = [
            'name.required'  => '请填写类别值名称',
            'type.required'  => '类别属性值名称不能为空',
            'type.integer'   => '类别属性值名称不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->addPreferenceValue($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/oa/del_preference_value",
     *     tags={"OA成员类别管理"},
     *     summary="成员类别属性删除",
     *     operationId="del_preference_value",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function delPreferenceValue(){
        $rules = [
            'id'     => 'required|integer',
        ];
        $messages = [
            'id.required'  => '类别值名称不能为空',
            'id.integer'   => '类别值名称不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->delPreferenceValue($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/edit_preference_value",
     *     tags={"OA成员类别管理"},
     *     summary="成员活动偏好类别属性修改",
     *     operationId="edit_preference_value",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类别id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="类别值名称",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="content[类别值介绍]",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function editPreferenceValue(){
        $rules = [
            'id'     => 'required|integer',
            'name'   => 'required',
        ];
        $messages = [
            'name.required'=> '请填写类别值名称',
            'id.required'  => '类别值ID不能为空',
            'id.integer'   => '类别值ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->editPreferenceValue($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_preference_value_info",
     *     tags={"OA成员类别管理"},
     *     summary="根据ID获取成员类别属性信息",
     *     operationId="get_preference_value_info",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类别id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getPreferenceValueInfo(){
        $rules = [
            'id'     => 'required|integer',
        ];
        $messages = [
            'id.required'  => '类别ID不能为空',
            'id.integer'   => '类别ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->getPreferenceValueInfo($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_preference_value_list",
     *     tags={"OA成员类别管理"},
     *     summary="获取成员类别属性列表",
     *     operationId="get_preference_value_list",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="type",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getPreferenceValueList(){
        $rules = [
            'type'     => 'required|integer',
        ];
        $messages = [
            'type.required'  => '类别属性ID不能为空',
            'type.integer'   => '类别属性ID不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->getPreferenceValueList($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message,'data' => $res];
    }













    /**
     * @OA\Get(
     *     path="/api/v1/oa/add_member_preferences",
     *     tags={"OA成员管理"},
     *     summary="添加成员活动偏好",
     *     operationId="add_member_preferences",
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
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="type[类型，1工作类，2社交类，3生活类，4艺术类，5感兴趣活动]",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="name[名称]",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="content[偏好内容简介]",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function addMemberPreferences(){
        $rules = [
            'name'     => 'required',
            'type'     => 'required|in:1,2,3,4,5',
        ];
        $messages = [
            'name.required'  => '请填写类别名称',
            'type.required'  => '类型不能为空',
            'type.in'        => '请填写生日',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->MemberPreferenceService->addMemberPreferences($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->MemberPreferenceService->error];
        }
        return ['code' => 200, 'message' => $this->MemberPreferenceService->message,'data' => $res];
    }
}