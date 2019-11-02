<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\OrdersService;

class DoctorOrderController extends ApiController
{
    public $OrdersService;

    /**
     * DoctorsController constructor.
     * @param $OrdersService
     */
    public function __construct(OrdersService $OrdersService)
    {
        parent::__construct();
        $this->OrdersService = $OrdersService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_doctorOrders",
     *     tags={"医疗医院前端"},
     *     summary="添加预约",
     *     description="jing" ,
     *     operationId="add_doctorOrders",
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
     *         name="name",
     *         in="query",
     *         description="预约人姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="性别[1 男 2女]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="age",
     *         in="query",
     *         description="年龄",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hospitals_id",
     *         in="query",
     *         description="预约医院id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="预约医生id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="appointment_at",
     *         in="query",
     *         description="预约时间",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="预约截止时间[可以不填写]",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *  ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="病史描述",
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
    public function addDoctorOrder(){
        $rules = [
            'name'              => 'required|string',
            'sex'               => 'required|integer',
            'age'               => 'required|integer',
            'doctor_id'         => 'required|integer',
            'description'       => 'string',
            'appointment_at'    => 'required|date',
            'end_time'          => 'required|date',
            'recommend'         => 'required|integer',
            'hospitals_id'      => 'required|integer',
            'department_ids'    => 'string',
        ];
        $messages = [
            'name.required'             => '预约人姓名不能为空',
            'name.string'               => '预约人姓名必须为字符串',
            'sex.required'              => '预约人性别不能为空',
            'sex.integer'               => '预约人姓名不是整数',
            'age.required'              => '预约人性别不能为空',
            'age.integer'               => '预约人姓名不是整数',
            'description.string'        => '病情描述必须为字符串',
            'hospitals_id.integer'      => '医院ID不是整数',
            'hospitals_id.required'     => '医院ID不能为空',
            'doctor_id.integer'         => '医生ID不是整数',
            'doctor_id.required'        => '医生ID不能为空',
            'appointment_at.date'       => '预约时间',
            'appointment_at.required'   => '医院ID不能为空',
            'end_time.integer'          => '医院ID不是整数',
            'end_time.required'         => '医院ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->addDoctors($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->OrdersService->message];
        }
        return ['code' => 100, 'message' => $this->OrdersService->error];
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
        $res = $this->OrdersService->deleteDoctors($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->OrdersService->message];
        }
        return ['code' => 100, 'message' => $this->OrdersService->error];
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
        $res = $this->OrdersService->editDoctors($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->OrdersService->message];
        }
        return ['code' => 100, 'message' => $this->OrdersService->error];
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
        $res = $this->OrdersService->getDoctorsListPage($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message,'data' => $res];
    }

}