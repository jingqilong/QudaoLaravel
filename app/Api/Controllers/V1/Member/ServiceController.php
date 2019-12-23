<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\ServiceConsumeService;
use App\Services\Member\ServiceService;
use Illuminate\Http\JsonResponse;

class ServiceController extends ApiController
{
    protected $serviceService;
    protected $serviceConsumeService;

    /**
     * TestApiController constructor.
     * @param ServiceService $serviceService
     * @param ServiceConsumeService $serviceConsumeService
     */
    public function __construct(ServiceService $serviceService,
                                ServiceConsumeService $serviceConsumeService)
    {
        parent::__construct();
        $this->serviceService           = $serviceService;
        $this->serviceConsumeService    = $serviceConsumeService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_service",
     *     tags={"会员权限"},
     *     summary="添加服务",
     *     description="只添加服务种类,sang",
     *     operationId="add_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="父级服务ID，不填为顶级目录",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="服务名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="服务介绍",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function addService()
    {
        $rules = [
            'parent_id' => 'integer',
            'name'      => 'required|string',
            'desc'      => 'required|string',
        ];
        $messages = [
            'parent_id.integer' => '父级服务ID必须为整数',
            'name.required'     => '请输入服务名称',
            'name.string'       => '服务名称只能是字符串',
            'desc.required'     => '请输入服务说明',
            'desc.string'       => '服务说明只能是字符串',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->addService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/service_detail",
     *     tags={"会员权限"},
     *     summary="获取服务详情",
     *     description="获取服务详情,sang",
     *     operationId="service_detail",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function serviceDetail()
    {
        $rules = [
            'service_id'      => 'required|integer',
        ];
        $messages = [
            'service_id.required' => '服务ID不能为空',
            'service_id.integer' => '服务ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->serviceDetail($this->request['service_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message, 'data' => $res];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/member/delete_service",
     *     tags={"会员权限"},
     *     summary="删除服务",
     *     description="只删除服务种类,sang",
     *     operationId="delete_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function deleteService()
    {
        $rules = [
            'service_id'      => 'required|integer',
        ];
        $messages = [
            'service_id.required' => '服务ID不能为空',
            'service_id.integer' => '服务ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->deleteService($this->request['service_id']);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }



    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_service",
     *     tags={"会员权限"},
     *     summary="修改服务",
     *     description="用于修改服务信息,sang",
     *     operationId="edit_service",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="服务名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="服务介绍",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function editService()
    {
        $rules = [
            'service_id'=> 'required|integer',
            'name'      => 'required|string',
            'desc'      => 'required|string',
        ];
        $messages = [
            'service_id.required'   => '服务ID不能为空',
            'service_id.integer'    => '服务ID必须为整数',
            'name.required'         => '请输入服务名称',
            'name.string'           => '服务名称只能是字符串',
            'desc.required'         => '请输入服务说明',
            'desc.string'           => '服务说明只能是字符串',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceService->editService($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/service_list",
     *     tags={"会员权限"},
     *     summary="获取服务列表",
     *     description="获取所有的服务列表,sang",
     *     operationId="service_list",
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
     *         description="用户token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function serviceList()
    {
        $res = $this->serviceService->serviceList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->serviceService->error];
        }
        return ['code' => 200, 'message' => $this->serviceService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_service_record",
     *     tags={"会员权限"},
     *     summary="添加会员服务消费记录",
     *     description="sang",
     *     operationId="add_service_record",
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
     *         description="会员ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="服务ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="消费数量",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="remark",
     *         in="query",
     *         description="备注",
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
    public function addServiceRecord(){
        $rules = [
            'member_id'     => 'required|integer',
            'service_id'    => 'required|integer',
            'number'        => 'required|integer',
            'remark'        => 'max:500',
        ];
        $messages = [
            'member_id.required'    => '会员ID不能为空',
            'member_id.integer'     => '会员ID必须为整数',
            'service_id.required'   => '服务ID不能为空',
            'service_id.integer'    => '服务ID必须为整数',
            'number.required'       => '服务次数不能为空',
            'number.string'         => '服务次数必须为整数',
            'remark.required'       => '备注字数不能超过500字',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->serviceConsumeService->addServiceRecord($this->request);
        if (!$res){
            return ['code' => 100, 'message' => $this->serviceConsumeService->error];
        }
        return ['code' => 200, 'message' => $this->serviceConsumeService->message];
    }
}