<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\TowardService;

class TowardController extends ApiController
{
    public $towardService;

    /**
     * FacilityController constructor.
     * @param $towardService
     */
    public function __construct(TowardService $towardService)
    {
        parent::__construct();
        $this->towardService = $towardService;
    }



    /**
     * @OA\Post(
     *     path="/api/v1/house/add_toward",
     *     tags={"房产租赁后台"},
     *     summary="添加房产朝向",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="add_toward",
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
     *         description="朝向标题",
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
    public function addToward(){
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
        $res = $this->towardService->addToward($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->towardService->message];
        }
        return ['code' => 100, 'message' => $this->towardService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_toward",
     *     tags={"房产租赁后台"},
     *     summary="删除房产朝向",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="delete_toward",
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
     *         description="朝向id",
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
    public function deleteToward(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '朝向ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->towardService->deleteToward($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->towardService->message];
        }
        return ['code' => 100, 'message' => $this->towardService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_toward",
     *     tags={"房产租赁后台"},
     *     summary="修改房产朝向",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="edit_toward",
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
     *         description="朝向ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="朝向标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="朝向说明",
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
    public function editToward(){
        $rules = [
            'id'            => 'required|integer',
            'title'         => 'required',
        ];
        $messages = [
            'id.required'           => '朝向ID不能为空',
            'id.integer'            => '朝向ID必须为整数',
            'title.required'        => '朝向标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->towardService->editToward($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->towardService->message];
        }
        return ['code' => 100, 'message' => $this->towardService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/toward_list",
     *     tags={"房产租赁后台"},
     *     summary="获取房产朝向列表",
     *     deprecated=true,
     *     description="sang" ,
     *     operationId="toward_list",
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
    public function towardList(){
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
        $res = $this->towardService->getTowardList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->towardService->error];
        }
        return ['code' => 200, 'message' => $this->towardService->message,'data' => $res];
    }
}