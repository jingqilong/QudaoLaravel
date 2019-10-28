<?php


namespace App\Api\Controllers\V1\Enterprise;


use App\Api\Controllers\ApiController;
use App\Services\Enterprise\OrderService;

class EnterpriseController extends ApiController
{

    protected $enterpriseService;

    public function __construct(OrderService $enterpriseService)
    {
        parent::__construct();
        $this->enterpriseService   =  $enterpriseService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/enterprise/get_enterprise_list",
     *     tags={"企业咨询(前端页面)"},
     *     summary="获取本人企业咨询订单列表(前端)",
     *     operationId="get_enterprise_list",
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
     *      @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索内容【项目名称，服务类型】",
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
     *     @OA\Response(response=100,description="获取企业咨询订单列表失败",),
     * )
     *
     */
    public function getEnterpriseList()
    {
        $rules = [
            'keywords'          => 'string',
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'keywords.string'           => '请正确输入搜索条件',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $list = $this->enterpriseService->getEnterpriseList($this->request);

        return ['code' => 200, 'message' => $this->enterpriseService->message,'data' => $list];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/enterprise/get_enterprise_info",
     *     tags={"企业咨询(前端页面)"},
     *     summary="获取企业咨询订单",
     *     operationId="get_enterprise_info",
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
     *         name="id",
     *         in="query",
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取企业咨询订单失败",),
     * )
     *
     */
    public function getEnterpriseInfo()
    {
        $rules = [
            'id'              => 'required|integer',
        ];
        $messages = [
            'id.required'             => '请传值id',
            'id.integer'              => '请正确传值id',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $id   = $this->request['id'];
        $list = $this->enterpriseService->getEnterpriseInfo($id);
        if (!$list){
            return ['code' => 100, 'message' => $this->enterpriseService->error];
        }
        return ['code' => 200, 'message' => $this->enterpriseService->message,'data' => $list];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/enterprise/add_enterprise",
     *     tags={"企业咨询(前端页面)"},
     *     summary="添加企业咨询订单信息",
     *     operationId="add_enterprise",
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
     *          name="enterprise_name",
     *          in="query",
     *          description="企业咨询名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="service_type",
     *          in="query",
     *          description="服务类型",
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
     *     @OA\Response(response=100,description="添加企业咨询订单失败",),
     * )
     *
     */
    public function addEnterprise()
    {
        $rules = [
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'enterprise_name'   => 'required',
            'service_type'      => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'enterprise_name.required'  => '请输入企业咨询名称',
            'service_type.required'     => '请输入企业咨询服务类型',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->enterpriseService->addEnterprise($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->enterpriseService->error];
        }
        return ['code' => 200, 'message' => $this->enterpriseService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/enterprise/upd_enterprise",
     *     tags={"企业咨询(前端页面)"},
     *     summary="修改企业咨询订单信息",
     *     operationId="upd_enterprise",
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
     *          description="订单ID",
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
     *          name="enterprise_name",
     *          in="query",
     *          description="企业咨询名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="service_type",
     *          in="query",
     *          description="服务类型",
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
     *     @OA\Response(response=100,description="修改企业咨询订单失败",),
     * )
     *
     */
    public function updEnterprise()
    {
        $rules = [
            'id'                => 'required',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[345678][0-9]{9}$/',
            'enterprise_name'   => 'required',
            'service_type'      => 'required',
            'reservation_at'    => 'required|date',
        ];
        $messages = [
            'id.required'               => '无法获取到订单id',
            'name.required'             => '请输入预约姓名',
            'mobile.required'           => '请填写预约手机号',
            'mobile.regex'              => '请正确填写手机号',
            'enterprise_name.required'  => '请输入企业咨询名称',
            'service_type.required'     => '请输入企业咨询服务类型',
            'reservation_at.required'   => '请输入预约时间',
            'reservation_at.date'       => '请输入正确预约时间',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->enterpriseService->updEnterprise($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->enterpriseService->error];
        }
        return ['code' => 200, 'message' => $this->enterpriseService->message];
    }




    /**
     * @OA\Delete(
     *     path="/api/v1/enterprise/del_enterprise",
     *     tags={"企业咨询(前端页面)"},
     *     summary="删除企业咨询订单信息",
     *     operationId="del_enterprise",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
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
     *          description="企业咨询订单id",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除企业咨询订单失败",),
     * )
     *
     */
    public function delEnterprise()
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
        $res = $this->enterpriseService->delEnterprise($id);
        if (!$res){
            return ['code' => 100, 'message' => $this->enterpriseService->error];
        }
        return ['code' => 200, 'message' => $this->enterpriseService->message];
    }

}