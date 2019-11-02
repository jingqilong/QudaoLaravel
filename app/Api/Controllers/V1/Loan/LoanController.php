<?php


namespace App\Api\Controllers\V1\Loan;


use App\Api\Controllers\ApiController;
use App\Services\Loan\PersonalService;

class LoanController extends ApiController
{
    protected $personalService;


    public function __construct(PersonalService $personalService)
    {
        parent::__construct();
        $this->personalService = $personalService;
    }


    /**
     * @OA\Get(
     *     path="/api/v1/loan/get_loan_list",
     *     tags={"贷款"},
     *     summary="获取贷款订单列表（前端使用）",
     *     operationId="get_loan_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="成员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="操作类型(1本人预约;2推荐预约)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取贷款订单列表失败",),
     * )
     *
     */
    public function getLoanList()
    {
        $rules = [
            'type'              => 'required|between:1,2',
        ];
        $messages = [
            'type.required'             => '请输入操作类型',
            'type.between'              => '操作类型不正确',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->personalService->getLoanList($this->request);
        return ['code' => 200, 'message' => $this->personalService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/loan/get_loan_order_list",
     *     tags={"贷款后台"},
     *     summary="获取所有贷款订单列表（后台使用）",
     *     operationId="get_loan_order_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="排序方式[1 正序 2 倒叙]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="预约方式[1 本人预约 2 推荐人预约]",
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
     *     @OA\Response(response=100,description="获取贷款订单列表失败",),
     * )
     *
     */
    public function getLoanOrderList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'type'          => 'in:1,2',
        ];
        $messages = [
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
            'type.in'                   => '推荐类型不存在',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->personalService->getLoanOrderList($this->request);
        if (!$list){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/loan/get_loan_info",
     *     tags={"贷款"},
     *     summary="根据ID获取贷款订单信息",
     *     operationId="get_loan_info",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="成员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="根据ID获取贷款订单信息",),
     * )
     *
     */
    public function getLoanInfo()
    {
        $rules = [
            'id'                => 'required|integer',
        ];
        $messages = [
            'id.required'       => '请输入订单ID',
            'id.integer'        => '订单ID必须为整数',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->personalService->getLoanInfo($this->request['id']);
        if (!$list){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message,'data' => $list];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/loan/get_loan_order_info",
     *     tags={"贷款后台"},
     *     summary="根据ID查找贷款订单信息",
     *     operationId="get_loan_order_info",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取贷款订单信息",),
     * )
     *
     */
    public function getLoanOrderInfo()
    {
        $rules = [
            'id'                => 'required',
        ];
        $messages = [
            'id.required'               => '请输入订单ID',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->personalService->getLoanOrderInfo($this->request);
        if (!$list){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message,'data' => $list];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/loan/add_loan",
     *     tags={"贷款"},
     *     summary="添加贷款订单信息",
     *     operationId="add_loan",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="成员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="操作类型(1本人预约;2推荐预约)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="姓名",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="mobile",
     *          in="query",
     *          description="手机号",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="ent_name",
     *          in="query",
     *          description="企业名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="ent_title",
     *          in="query",
     *          description="职位",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="贷款金额",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="面谈地址",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="reservation_at",
     *          in="query",
     *          description="预约时间",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="remark",
     *          in="query",
     *          description="备注",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加贷款订单失败",),
     * )
     *
     */
    public function addLoan()
    {
        $rules = [
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'price'             => 'required|alpha_num',
            'ent_name'          => 'required',
            'ent_title'         => 'required',
            'address'           => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'price.required'            => '请正确填写贷款金额',
            'price.alpha_num'           => '金额数量不正确',
            'ent_name.required'         => '请输入企业名称',
            'ent_title.required'        => '请输入职位',
            'address.required'          => '请输入面谈地址',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->personalService->addLoan($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/loan/upd_loan",
     *     tags={"贷款后台"},
     *     summary="修改贷款订单信息",
     *     operationId="upd_loan",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *          name="type",
     *          in="query",
     *          description="操作类型(1本人预约;2推荐预约)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="订单ID",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="姓名或被推荐人姓名",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="mobile",
     *          in="query",
     *          description="手机号或被推荐人手机号",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="ent_name",
     *          in="query",
     *          description="推荐人企业名称或被推荐人推荐人企业名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="ent_title",
     *          in="query",
     *          description="推荐人职位或被推荐人职位",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="贷款金额",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="面谈地址",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="reference_name",
     *          in="query",
     *          description="推荐人姓名",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="cardid",
     *          in="query",
     *          description="推荐人卡号",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="reservation_at",
     *          in="query",
     *          description="预约时间",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="remark",
     *          in="query",
     *          description="备注",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态【1 已提交 2审核中 3 审核失败 4审核通过 9 已删除】",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="修改贷款订单失败",),
     * )
     *
     */
    public function updLoan()
    {
        $rules = [
            'id'                => 'required',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'price'             => 'required|alpha_num',
            'ent_name'          => 'required',
            'ent_title'         => 'required',
            'address'           => 'required',
            'cardid'            => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'id.required'               => '获取订单ID',
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'price.required'            => '请正确填写贷款金额',
            'price.alpha_num'           => '金额数量不正确',
            'ent_name.required'         => '请输入企业名称',
            'ent_title.required'        => '请输入职位',
            'address.required'          => '请输入面谈地址',
            'cardid.required'           => '获取预约人的卡号',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->personalService->updLoan($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message];
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/loan/del_loan",
     *     tags={"贷款后台"},
     *     summary="删除贷款订单信息",
     *     operationId="del_loan",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
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
     *          name="id",
     *          in="query",
     *          description="贷款订单id",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除贷款订单失败",),
     * )
     *
     */
    public function delLoan()
    {
        $rules = [
            'id'      => 'required',
        ];
        $messages = [
            'id.required'     => '找不到该ID！',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id = $this->request['id'];
        $res = $this->personalService->delLoan($id);
        if (!$res){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/loan/audit_loan",
     *     tags={"贷款后台"},
     *     summary="审核预约贷款",
     *     description="jing" ,
     *     operationId="audit_loan",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="预约订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit",
     *         in="query",
     *         description="审核结果，1通过，2驳回",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="审核失败",
     *     ),
     * )
     *
     */
    public function auditLoan(){
        $rules = [
            'id'            => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'id.required'               => '贷款ID不能为空',
            'id.integer'                => '贷款ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->personalService->auditLoan($this->request['id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message];
    }
}