<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\FacilitiesService;

class FacilityController extends ApiController
{
    public $facilityService;

    /**
     * FacilityController constructor.
     * @param $facilityService
     */
    public function __construct(FacilitiesService $facilityService)
    {
        parent::__construct();
        $this->facilityService = $facilityService;
    }



    /**
     * @OA\Post(
     *     path="/api/v1/house/add_facility",
     *     tags={"房产租赁后台"},
     *     summary="添加房产设施",
     *     description="sang" ,
     *     operationId="add_facility",
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
     *         description="设施标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon_id",
     *         in="query",
     *         description="设施图标ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
    public function addFacility(){
        $rules = [
            'title'         => 'required',
            'icon_id'       => 'required|integer',
        ];
        $messages = [
            'title.required'        => '设施标题不能为空',
            'icon_id.required'      => '设施图标不能为空',
            'icon_id.integer'       => '设施图标ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->facilityService->addFacility($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->facilityService->message];
        }
        return ['code' => 100, 'message' => $this->facilityService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_facility",
     *     tags={"房产租赁后台"},
     *     summary="删除房产设施",
     *     description="sang" ,
     *     operationId="delete_facility",
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
     *         description="设施id",
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
    public function deleteFacility(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '设施ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->facilityService->deleteFacility($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->facilityService->message];
        }
        return ['code' => 100, 'message' => $this->facilityService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_facility",
     *     tags={"房产租赁后台"},
     *     summary="修改房产设施",
     *     description="sang" ,
     *     operationId="edit_facility",
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
     *         description="设施ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="设施标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="设施说明",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon_id",
     *         in="query",
     *         description="设施图标ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editFacility(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'icon_id'       => 'required|integer',
        ];
        $messages = [
            'id.required'           => '设施ID不能为空',
            'id.integer'            => '设施ID必须为整数',
            'name.required'         => '设施标题不能为空',
            'icon_id.required'      => '设施图标不能为空',
            'icon_id.integer'       => '设施图标ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->facilityService->editFacility($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->facilityService->message];
        }
        return ['code' => 100, 'message' => $this->facilityService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/facility_list",
     *     tags={"房产租赁后台"},
     *     summary="获取房产设施列表",
     *     description="sang" ,
     *     operationId="facility_list",
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
    public function facilityList(){
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
        $res = $this->facilityService->getFacilityList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->facilityService->error];
        }
        return ['code' => 200, 'message' => $this->facilityService->message,'data' => $res];
    }
}