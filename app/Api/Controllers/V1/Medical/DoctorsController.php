<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\DoctorsService;

class DoctorsController extends ApiController
{
    public $doctorsService;

    /**
     * DoctorsController constructor.
     * @param $doctorsService
     */
    public function __construct(DoctorsService $doctorsService)
    {
        parent::__construct();
        $this->doctorsService = $doctorsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_doctors",
     *     tags={"医疗医院后台"},
     *     summary="添加医生",
     *     description="jing" ,
     *     operationId="add_doctors",
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
     *         description="会员id【是会员需要传入】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="医生姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="img_id",
     *         in="query",
     *         description="医生照片",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="医生性别[1 男 2女]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="职称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="good_at",
     *         in="query",
     *         description="擅长介绍",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="label_ids",
     *         in="query",
     *         description="医生标签ids【格式：1,2,3,4】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="introduction",
     *         in="query",
     *         description="简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *  ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="推荐[0 不推荐 1推荐]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hospitals_id",
     *         in="query",
     *         description="医院ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="department_ids",
     *         in="query",
     *         description="科室ids【格式：1,2,3,4】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addDoctors(){
        $rules = [
            'member_id'         => 'integer',
            'title'             => 'required|string',
            'name'              => 'required|string',
            'img_id'            => 'required|integer',
            'sex'               => 'required|integer',
            'good_at'           => 'required|string',
            'introduction'      => 'string',
            'label_ids'         => 'string',
            'recommend'         => 'required|integer',
            'hospitals_id'      => 'required|integer',
            'department_ids'    => 'string',
        ];
        $messages = [
            'member_id.integer'         => '会员id必须为整数',
            'name.required'             => '医生姓名不能为空',
            'name.string'               => '医生姓名必须为字符串',
            'img_id.required'           => '医生头像不能为空',
            'img_id.integer'            => '医生头像必须为整数',
            'title.string'              => '医生职称必须为字符串',
            'title.required'            => '医生职称不能为空',
            'sex.required'              => '医生性别不能为空',
            'sex.integer'               => '医生性别必须为整数',
            'good_at.string'            => '擅长介绍必须为字符串',
            'good_at.required'          => '擅长介绍不能为空',
            'introduction.string'       => '简介必须为字符串',
            'label_ids.string'          => '照片上传格式不正确',
            'hospitals_id.integer'      => '医院ID不是整数',
            'hospitals_id.required'     => '医院ID不能为空',
            'department_id.string'      => '科室ids格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->addDoctors($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->doctorsService->message];
        }
        return ['code' => 100, 'message' => $this->doctorsService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/medical/delete_doctors",
     *     tags={"医疗医院后台"},
     *     summary="删除医生信息",
     *     description="jing" ,
     *     operationId="delete_doctors",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="医生id",
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
    public function deleteDoctors(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '医生ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->deleteDoctors($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->doctorsService->message];
        }
        return ['code' => 100, 'message' => $this->doctorsService->error];
    }

    /**
     * @OA\get(
     *     path="/api/v1/medical/search_doctors_hospitals",
     *     tags={"医疗医院前端"},
     *     summary="获取医生或者医院列表",
     *     description="jing" ,
     *     operationId="search_doctors_hospitals",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="搜索类型 【1医院 2医生】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索条件 【医院:(医院名字 擅长 获奖情况 详细地址)  医生:(医生姓名，医生性别，医生科室)】",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
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
    public function searchDoctorsHospitals(){
        $rules = [
            'type'          => 'in:1,2',
        ];
        $messages = [
            'id.in'         => '搜索类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->searchDoctorsHospitals($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->doctorsService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->doctorsService->error];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/medical/edit_doctors",
     *     tags={"医疗医院后台"},
     *     summary="更改医生信息",
     *     description="jing" ,
     *     operationId="edit_doctors",
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
     *         description="医生ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="医生姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="img_id",
     *         in="query",
     *         description="医生头像",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="医生性别[1 男 2女]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="职称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="good_at",
     *         in="query",
     *         description="擅长介绍",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="label_ids",
     *         in="query",
     *         description="医生标签ids【格式：1,2,3,4】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="introduction",
     *         in="query",
     *         description="简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *  ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="推荐[0 不推荐 1推荐]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hospitals_id",
     *         in="query",
     *         description="医院ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="department_ids",
     *         in="query",
     *         description="科室ids【格式：1,2,3,4】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function editDoctors(){
        $rules = [
            'id'                => 'required|integer',
            'title'             => 'required|string',
            'name'              => 'required|string',
            'img_id'            => 'required|integer',
            'sex'               => 'required|integer',
            'good_at'           => 'required|string',
            'introduction'      => 'string',
            'label_ids'         => 'string',
            'recommend'         => 'required|integer',
            'hospitals_id'      => 'required|integer',
            'department_ids'    => 'string',
        ];
        $messages = [
            'id.required'               => '医生ID不能为空',
            'id.integer'                => '医生ID必须为整数',
            'name.required'             => '医生姓名不能为空',
            'name.string'               => '医生姓名必须为字符串',
            'img_id.required'           => '医生头像不能为空',
            'img_id.integer'            => '医生头像必须为整数',
            'title.string'              => '医生职称必须为字符串',
            'title.required'            => '医生职称不能为空',
            'sex.required'              => '医生性别不能为空',
            'sex.integer'               => '医生姓名不是整数',
            'good_at.string'            => '擅长介绍必须为字符串',
            'good_at.required'          => '擅长介绍不能为空',
            'introduction.string'       => '简介必须为字符串',
            'label_ids.string'          => '照片上传格式不正确',
            'hospitals_id.integer'      => '医院ID不是整数',
            'hospitals_id.required'     => '医院ID不能为空',
            'department_id.string'      => '科室ids格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->editDoctors($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->doctorsService->message];
        }
        return ['code' => 100, 'message' => $this->doctorsService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctors_list_page",
     *     tags={"医疗医院后台"},
     *     summary="获取医生列表(oa)",
     *     description="jing" ,
     *     operationId="doctors_list_page",
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
     *         description="搜索内容【医生姓名，医生性别，医生科室】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="排序方式【1 时间正序 2 时间倒叙 默认为1 】",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function doctorsListPage(){
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
        $res = $this->doctorsService->getDoctorsListPage($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->doctorsService->error];
        }
        return ['code' => 200, 'message' => $this->doctorsService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/get_doctor",
     *     tags={"医疗医院前端"},
     *     summary="用户获取医生详情",
     *     description="jing" ,
     *     operationId="get_doctor",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="医生id",
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
    public function getDoctor(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.integer'        => '医生id必须为整数',
            'id.required'       => '医生id不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->getDoctorById($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->doctorsService->error];
        }
        return ['code' => 200, 'message' => $this->doctorsService->message,'data' => $res];
    }

 /**
     * @OA\Get(
     *     path="/api/v1/medical/get_departments_doctor",
     *     tags={"医疗医院前端"},
     *     summary="用户根据科室获取医生列表",
     *     description="jing" ,
     *     operationId="get_departments_doctor",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="医院ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departments_id",
     *         in="query",
     *         description="科室ID",
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
    public function getDepartmentsDoctor(){
        $rules = [
            'departments_id'       => 'required|integer',
            'doctor_id'            => 'required|integer',
        ];
        $messages = [
            'departments_id.integer'   => '科室id必须为整数',
            'departments_id.required'  => '科室id不能为空',
            'doctor_id.integer'        => '医生id必须为整数',
            'doctor_id.required'       => '医生id不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->getDepartmentsDoctor($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->doctorsService->error];
        }
        return ['code' => 200, 'message' => $this->doctorsService->message,'data' => $res];
    }

}