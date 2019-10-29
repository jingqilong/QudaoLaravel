<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\UnitService;

class UnitController extends ApiController
{
    public $unitService;

    /**
     * FacilityController constructor.
     * @param UnitService $unitService
     */
    public function __construct(UnitService $unitService)
    {
        parent::__construct();
        $this->unitService = $unitService;
    }



    /**
     * @OA\Post(
     *     path="/api/v1/house/add_unit",
     *     tags={"房产租赁后台"},
     *     summary="添加房产户型",
     *     description="sang" ,
     *     operationId="add_unit",
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
     *         description="户型标题",
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
    public function addUnit(){
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
        $res = $this->unitService->addUnit($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->unitService->message];
        }
        return ['code' => 100, 'message' => $this->unitService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_unit",
     *     tags={"房产租赁后台"},
     *     summary="删除房产户型",
     *     description="sang" ,
     *     operationId="delete_unit",
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
     *         description="户型id",
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
    public function deleteUnit(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '户型ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->unitService->deleteUnit($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->unitService->message];
        }
        return ['code' => 100, 'message' => $this->unitService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_unit",
     *     tags={"房产租赁后台"},
     *     summary="修改房产户型",
     *     description="sang" ,
     *     operationId="edit_unit",
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
     *         description="户型ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="户型标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="户型说明",
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
    public function editUnit(){
        $rules = [
            'id'            => 'required|integer',
            'title'         => 'required',
        ];
        $messages = [
            'id.required'           => '户型ID不能为空',
            'id.integer'            => '户型ID必须为整数',
            'title.required'        => '户型标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->unitService->editUnit($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->unitService->message];
        }
        return ['code' => 100, 'message' => $this->unitService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/unit_list",
     *     tags={"房产租赁后台"},
     *     summary="获取房产户型列表",
     *     description="sang" ,
     *     operationId="unit_list",
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
    public function unitList(){
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
        $res = $this->unitService->getUnitList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->unitService->error];
        }
        return ['code' => 200, 'message' => $this->unitService->message,'data' => $res];
    }
}