<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medicla\HospitalsService;

class HospitalsController extends ApiController
{
    public $HospitalsServices;

    /**
     * HospitalsController constructor.
     * @param $HospitalsServices
     */
    public function __construct(HospitalsService $HospitalsServices)
    {
        parent::__construct();
        $this->HospitalsServices = $HospitalsServices;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_hospitals",
     *     tags={"医疗医院后台"},
     *     summary="添加医疗医院",
     *     description="jing" ,
     *     operationId="add_hospitals",
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
     *         name="name",
     *         in="query",
     *         description="医疗医院名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="introduction",
     *         in="query",
     *         description="简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="推荐[0 不推荐 1 推荐 ]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addHospitals(){
        $rules = [
            'name'         => 'required',
            'recommend'    => 'required|integer',
        ];
        $messages = [
            'name.required'        => '医疗医院标题不能为空',
            'recommend.required'   => '医疗医院推荐不能为空',
            'recommend.integer'    => '医疗医院推荐码不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->HospitalsServices->addHospitals($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->HospitalsServices->message];
        }
        return ['code' => 100, 'message' => $this->HospitalsServices->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/medical/delete_hospitals",
     *     tags={"医疗医院后台"},
     *     summary="删除医疗医院",
     *     description="jing" ,
     *     operationId="delete_hospitals",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="医疗医院id",
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
    public function deleteHospitals(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '医疗医院ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->HospitalsServices->deleteHospitals($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->HospitalsServices->message];
        }
        return ['code' => 100, 'message' => $this->HospitalsServices->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/medical/edit_hospitals",
     *     tags={"医疗医院后台"},
     *     summary="修改医疗医院",
     *     description="jing" ,
     *     operationId="edit_hospitals",
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
     *         description="oa token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="医疗医院ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="医院名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="introduction",
     *         in="query",
     *         description="简介",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="推荐[0 不推荐 1 推荐 ]",
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
    public function editHospitals(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
            'recommend'     => 'required|in:0,1',
        ];
        $messages = [
            'id.required'           => '医疗医院ID不能为空',
            'id.integer'            => '医疗医院ID必须为整数',
            'name.required'         => '医疗医院标题不能为空',
            'recommend.required'    => '医疗医院推荐不能为空',
            'recommend.in'          => '医疗医院推荐类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->HospitalsServices->editHospitals($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->HospitalsServices->message];
        }
        return ['code' => 100, 'message' => $this->HospitalsServices->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/hospitals_list",
     *     tags={"医疗医院后台"},
     *     summary="获取医疗医院列表",
     *     description="jing" ,
     *     operationId="hospitals_list",
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
    public function hospitalsList(){
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
        $res = $this->HospitalsServices->getHospitalsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->HospitalsServices->error];
        }
        return ['code' => 200, 'message' => $this->HospitalsServices->message,'data' => $res];
    }
    
}