<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\HospitalsService;

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
     *         name="img_ids",
     *         in="query",
     *         description="照片ids  1,2,3,",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="query",
     *         description="擅长领域",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *
     *     ),
     *     @OA\Parameter(
     *         name="department_ids",
     *         in="query",
     *         description="科室 1,2,3,",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="awards",
     *         in="query",
     *         description="获奖情况",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
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
     *         name="area_code",
     *         in="query",
     *         description="地址地区代码，例如：【310000,310100,310106,310106013】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="详细地址，例如：延安西路300号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
            'name'              => 'required',
            'img_ids'           => 'required',
            'domain'            => 'required',
            'department_ids'    => 'required',
            'awards'            => 'required',
            'introduction'      => 'required',
            'recommend'         => 'required|in:1,2',
            'area_code'         => 'required|regex:/^(\d+[,])*\d+$/',
            'address'           => 'required',
        ];
        $messages = [
            'name.required'             => '医疗医院标题不能为空',
            'img_ids.required'          => '医疗医院照片不能为空',
            'domain.required'           => '医疗医院擅长领域不能为空',
            'department_ids.required'   => '医疗医院科室不能为空',
            'awards.required'           => '医疗医院获奖情况不能为空',
            'introduction.required'     => '医疗医院标题不能为空',
            'recommend.required'        => '医疗医院简介不能为空',
            'recommend.in'              => '医疗医院推荐不正确',
            'area_code.required'        => '地区编码不能为空',
            'area_code.regex'           => '地区编码格式有误',
            'address.required'          => '详细地址不能为空',
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
     *         name="area_code",
     *         in="query",
     *         description="地址地区代码，例如：【310000,310100,310106,310106013】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="详细地址，例如：延安西路300号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
            'area_code'     => 'required|regex:/^(\d+[,])*\d+$/',
            'address'       => 'required',
        ];
        $messages = [
            'id.required'           => '医疗医院ID不能为空',
            'id.integer'            => '医疗医院ID必须为整数',
            'name.required'         => '医疗医院标题不能为空',
            'recommend.required'    => '医疗医院推荐不能为空',
            'recommend.in'          => '医疗医院推荐类型不存在',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'address.required'      => '详细地址不能为空',
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
    /**
     * @OA\Get(
     *     path="/api/v1/medical/hospital_list",
     *     tags={"医疗医院前端"},
     *     summary="用户获取医疗医院列表",
     *     description="jing" ,
     *     operationId="hospital_list",
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
     *         description="用户 token",
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
    public function hospitalList(){
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
        $res = $this->HospitalsServices->getHospitalList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->HospitalsServices->error];
        }
        return ['code' => 200, 'message' => $this->HospitalsServices->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/hospital_detail",
     *     tags={"医疗医院前端"},
     *     summary="用户获取医疗医院详情",
     *     description="jing" ,
     *     operationId="hospital_detail",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="医院ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function hospitalDetail(){
        $rules = [
            'id'          => 'required|integer',
        ];
        $messages = [
            'id.required'   => '医院ID不能为空',
            'id.integer'    => '医院ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->HospitalsServices->getHospitalDetail($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->HospitalsServices->error];
        }
        return ['code' => 200, 'message' => $this->HospitalsServices->message,'data' => $res];
    }

}