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
     *     path="/api/v1/medical/add_doctor_order",
     *     tags={"医疗医院前端"},
     *     summary="添加预约",
     *     description="jing" ,
     *     operationId="add_doctor_order",
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
     *         name="mobile",
     *         in="query",
     *         description="预约人手机",
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
     *         name="hospital_id",
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
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="服务类型 1看病 2手术 3住院",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
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
    public function addDoctorOrder(){
        $rules = [
            'name'              => 'required|string',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'sex'               => 'required|integer',
            'age'               => 'required|integer',
            'doctor_id'         => 'required|integer',
            'type'              => 'required|in:1,2,3',
            'description'       => 'string',
            'appointment_at'    => 'required|date',
            'end_time'          => 'date',
            'hospital_id'       => 'required|integer',
            'department_ids'    => 'string',
        ];
        $messages = [
            'name.required'             => '预约人姓名不能为空',
            'name.string'               => '预约人姓名必须为字符串',
            'mobile.required'           => '预约手机不能为空',
            'mobile.regex'              => '预约手机格式不正确',
            'sex.required'              => '预约人性别不能为空',
            'sex.integer'               => '预约人姓名不是整数',
            'age.required'              => '预约人年龄不能为空',
            'age.integer'               => '年龄格式错误',
            'description.string'        => '病情描述详情必须为字符串',
            'hospital_id.integer'       => '医院ID不是整数',
            'hospital_id.required'      => '医院ID不能为空',
            'type.in'                   => '服务类型存在',
            'type.required'             => '服务类型不能为空',
            'doctor_id.integer'         => '医生ID不是整数',
            'doctor_id.required'        => '医生ID不能为空',
            'appointment_at.date'       => '预约时间格式不正确',
            'appointment_at.required'   => '预约时间不能为空',
            'end_time.date'             => '截止时间格式不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->addDoctorOrders($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->OrdersService->message];
        }
        return ['code' => 100, 'message' => $this->OrdersService->error];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctor_order_list",
     *     tags={"医疗医院后台"},
     *     summary="获取预约列表(oa)",
     *     description="jing" ,
     *     operationId="doctor_order_list",
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
     *         description="搜索内容【预约人姓名，手机号】",
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
     *         name="status",
     *         in="query",
     *         description="状态【0待审核 1 审核通过 2 审核驳回 】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="服务类型 【1看病 2手术 3住院】",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function doctorOrderList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'status'        => 'in:0,1,2',
            'type'          => 'in:1,2,3',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
            'status.in'                 => '状态值不存在',
            'type.in'                   => '服务类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->getDoctorOrderList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message,'data' => $res];
    }

  /**
     * @OA\Post(
     *     path="/api/v1/medical/set_doctor_order",
     *     tags={"医疗医院后台"},
     *     summary="审核预约列表状态(oa)",
     *     description="jing" ,
     *     operationId="set_doctor_order",
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
     *         description="预约订单id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="审核状态【1审核通过 2审核驳回】",
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
    public function setDoctorOrder(){
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'in:0,1,2',
        ];
        $messages = [
            'id.required'               => '预约id不能为空',
            'id.integer'                => '预约id不是整数',
            'status.in'                 => '审核类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->setDoctorOrder($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctors_order_list",
     *     tags={"医疗医院前端"},
     *     summary="获取成员自己预约列表状态",
     *     description="jing" ,
     *     operationId="doctors_order_list",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function doctorsOrderList(){
        $res = $this->OrdersService->doctorsOrderList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctors_list",
     *     tags={"医疗医院前端"},
     *     summary="成员获取医生列表",
     *     description="jing" ,
     *     operationId="doctors_list",
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
     *         name="hospital_id",
     *         in="query",
     *         description="医院id【填写医院id 则搜索该医院下面的所有医生 不填写则是全部】",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function doctorsList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'      => '页码必须为整数',
            'page_num.integer'  => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->doctorsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message,'data' => $res];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctors_order",
     *     tags={"医疗医院前端"},
     *     summary="根据id获取成员自己预约详情",
     *     description="jing" ,
     *     operationId="doctors_order",
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
     *         name="id",
     *         in="query",
     *         description="订单id",
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
    public function doctorsOrder(){
        $rules = [
            'id'          => 'required|integer',
        ];
        $messages = [
            'id.required'      => '订单id不能为空',
            'id.integer'       => '订单id必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->OrdersService->doctorsOrder($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->OrdersService->error];
        }
        return ['code' => 200, 'message' => $this->OrdersService->message,'data' => $res];
    }

}
