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
     *     tags={"Loan"},
     *     summary="获取贷款订单列表",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="名字",
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
    /**
     * @return array
     */
    public function getLoanList()
    {
        $rules = [
            'name'              => 'required',
            'type'              => 'required|between:1,2',
        ];
        $messages = [
            'name.required'             => '请输入预约姓名',
            'type.required'             => '请输入操作类型',
            'type.between'              => '操作类型不正确',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->personalService->getLoanList($this->request);
        if (!$list){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => $this->personalService->message,'data' => $list];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/loan/add_loan",
     *     tags={"Loan"},
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
     *         description="token",
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
     *     @OA\Response(response=100,description="添加贷款订单失败",),
     * )
     *
     */
    /**
     * @return array
     * @param 添加贷款订单
     */
    public function addLoan()
    {
        $rules = [
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[34578][0-9]{9}$/',
            'price'             => 'required|alpha_num',
            'ent_name'          => 'required',
            'ent_title'         => 'required',
            'address'           => 'required',
            'cardid'            => 'required',
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
            'cardid.required'           => '获取预约人的卡号',
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
     *     tags={"Loan"},
     *     summary="修改贷款订单信息",
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
     *         description="token",
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
     *     @OA\Response(response=100,description="添加贷款订单失败",),
     * )
     *
     */
    /**
     * @return array
     * @param 修改贷款订单
     */
    public function updLoan()
    {
        $rules = [
            'id'                => 'required',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[34578][0-9]{9}$/',
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
     *     tags={"Loan"},
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
     *         description="token",
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
    /**
     * @return array
     * @param 软删除贷款订单信息
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
}