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
     * @OA\Post(
     *     path="/api/v1/oa/add_loan_order",
     *     tags={"Loan"},
     *     summary="添加贷款订单信息",
     *     operationId="add_loan_order",
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
     *          description="提交人会员id(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="提交人会员名字(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="提交人会员手机号(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="num",
     *          in="query",
     *          description="提交数量(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="cardid",
     *          in="query",
     *          description="提交人会员卡号(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="提交人openid",
     *          in="query",
     *          description="提交人openid(前端传值)",
     *          required=true,
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
            'name'      => 'required',
            'phone'     => 'required|regex:/^1[34578][0-9]{9}$/',
            'num'       => 'required|alpha_num',
        ];
        $messages = [
            'name.required'     => '请输入姓名',
            'phone.required'    => '请填写您的手机号',
            'phone.regex'       => '请正确填写手机号',
            'num.alpha_num'     => '请填写正确数量',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->personalService->add($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/upd_loan",
     *     tags={"Loan"},
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="提交人会员id(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="提交人会员名字(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="提交人会员手机号(前端传值)",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="cardid",
     *          in="query",
     *          description="提交人会员卡号(前端传值)",
     *          required=true,
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
        $res = $this->personalService->add($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->personalService->error];
        }
        return ['code' => 200, 'message' => '添加成功！'];
    }
}