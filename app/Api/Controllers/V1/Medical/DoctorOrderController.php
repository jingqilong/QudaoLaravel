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
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
            'status.in'                 => '状态值不存在',
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

}
