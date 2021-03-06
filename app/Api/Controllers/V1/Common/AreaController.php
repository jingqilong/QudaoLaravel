<?php


namespace App\Api\Controllers\V1\Common;

use App\Api\Controllers\ApiController;
use App\Services\Common\AreaService;

class AreaController extends ApiController
{
    public $areaService;

    /**
     * QiNiuController constructor.
     * @param AreaService $areaService
     */
    public function __construct(AreaService $areaService)
    {
        parent::__construct();
        $this->areaService       = $areaService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_area_list",
     *     tags={"公共"},
     *     summary="获取省市区街道四级联动地区列表",
     *     description="sang" ,
     *     operationId="get_area_list",
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
     *         name="parent_code",
     *         in="query",
     *         description="父级地区代码，取省级为0",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getAreaList(){
        $rules = [
            'parent_code'   => 'required|integer',
        ];
        $messages = [
            'parent_code.required'  => '父级地区代码不能为空',
            'parent_code.integer'   => '父级地区代码必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->areaService->getAreaList($this->request['parent_code']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->areaService->error];
        }
        return ['code' => 200, 'message' => $this->areaService->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/common/set_area_img",
     *     tags={"公共"},
     *     summary="设置省市区地域的图片和备注",
     *     description="jing" ,
     *     operationId="set_area_img",
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
     *         description="地区ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_url",
     *         in="query",
     *         description="图片url",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="memo",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function setAreaImg(){
        $rules = [
            'id'   => 'required|integer',
        ];
        $messages = [
            'id.required'  => '地区id不能为空',
            'id.integer'   => '地区id必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->areaService->setAreaImg($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->areaService->error];
        }
        return ['code' => 200, 'message' => $this->areaService->message];
    }
}