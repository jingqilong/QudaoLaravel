<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\LeasingService;

class LeaseController extends ApiController
{
    public $leaseService;

    /**
     * FacilityController constructor.
     * @param LeasingService $leasingService
     */
    public function __construct(LeasingService $leasingService)
    {
        parent::__construct();
        $this->leaseService = $leasingService;
    }



    /**
     * @OA\Post(
     *     path="/api/v1/house/add_lease",
     *     tags={"房产租赁后台"},
     *     summary="添加房产租赁方式",
     *     description="sang" ,
     *     operationId="add_lease",
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
     *         name="title",
     *         in="query",
     *         description="租赁方式标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="描述",
     *         required=false,
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
    public function addLease(){
        $rules = [
            'title'         => 'required',
        ];
        $messages = [
            'title.required'        => '设施标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->leaseService->addLease($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->leaseService->message];
        }
        return ['code' => 100, 'message' => $this->leaseService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_lease",
     *     tags={"房产租赁后台"},
     *     summary="删除房产租赁方式",
     *     description="sang" ,
     *     operationId="delete_lease",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="租赁方式id",
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
    public function deleteLease(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '租赁方式ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->leaseService->deleteLease($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->leaseService->message];
        }
        return ['code' => 100, 'message' => $this->leaseService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_lease",
     *     tags={"房产租赁后台"},
     *     summary="修改房产租赁方式",
     *     description="sang" ,
     *     operationId="edit_lease",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="租赁方式ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="租赁方式标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="租赁方式说明",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editLease(){
        $rules = [
            'id'            => 'required|integer',
            'title'         => 'required',
        ];
        $messages = [
            'id.required'           => '租赁方式ID不能为空',
            'id.integer'            => '租赁方式ID必须为整数',
            'title.required'        => '租赁方式标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->leaseService->editLease($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->leaseService->message];
        }
        return ['code' => 100, 'message' => $this->leaseService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/lease_list",
     *     tags={"房产租赁后台"},
     *     summary="获取房产租赁方式列表",
     *     description="sang" ,
     *     operationId="lease_list",
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
     *         description="token",
     *         required=true,
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
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function leaseList(){
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
        $res = $this->leaseService->getLeaseList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->leaseService->error];
        }
        return ['code' => 200, 'message' => $this->leaseService->message,'data' => $res];
    }
}