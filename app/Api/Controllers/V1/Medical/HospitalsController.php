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
     *         name="category",
     *         in="query",
     *         description="医院类别【1公立 2私立 3综合】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         name="longitude",
     *         in="query",
     *         description="地标经度，例如：【121.48941】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="地标纬度，例如：【31.40527】",
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
            'category'          => 'required|in:1,2,3',
            'department_ids'    => 'required',
            'introduction'      => 'required',
            'recommend'         => 'required|in:0,1',
            'area_code'         => 'required|regex:/^(\d+[,])*\d+$/',
            'longitude'         => 'required',
            'latitude'          => 'required',
            'address'           => 'required',
        ];
        $messages = [
            'name.required'             => '医疗医院标题不能为空',
            'img_ids.required'          => '医疗医院照片不能为空',
            'category.required'         => '医疗医院类别不能为空',
            'category.in'               => '医疗医院类别不存在',
            'department_ids.required'   => '医疗医院科室不能为空',
            'introduction.required'     => '医疗医院标题不能为空',
            'recommend.required'        => '医疗医院简介不能为空',
            'recommend.in'              => '医疗医院推荐不正确',
            'area_code.required'        => '地区编码不能为空',
            'area_code.regex'           => '地区编码格式有误',
            'longitude.required'        => '经度不能为空',
            'latitude.required'         => '纬度不能为空',
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
            'id.required' => '医疗医院ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->HospitalsServices->deleteHospitals($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->HospitalsServices->message , 'data' => $res];
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
     *         name="category",
     *         in="query",
     *         description="医院类别【1公立 2私立 3综合】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         name="longitude",
     *         in="query",
     *         description="地标经度，例如：【121.48941】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="地标纬度，例如：【31.40527】",
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
            'category'      => 'required|in:1,2,3',
            'recommend'     => 'required|in:0,1',
            'area_code'     => 'required|regex:/^(\d+[,])*\d+$/',
            'longitude'     => 'required',
            'latitude'      => 'required',
            'address'       => 'required',
        ];
        $messages = [
            'id.required'           => '医疗医院ID不能为空',
            'id.integer'            => '医疗医院ID必须为整数',
            'category.required'     => '医疗医院类别不能为空',
            'category.in'           => '医疗医院类别不存在',
            'name.required'         => '医疗医院标题不能为空',
            'recommend.required'    => '医疗医院推荐不能为空',
            'recommend.in'          => '医疗医院推荐类型不存在',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'longitude.required'    => '经度不能为空',
            'latitude.required'     => '纬度不能为空',
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
     *      @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="医院类别【1,公立 2，私立 3综合】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="是否推荐【0 不推荐 1,推荐】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="department_ids",
     *         in="query",
     *         description="科室id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
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
            'category'      => 'in:1,2,3',
            'recommend'     => 'in:0,1',
            'department_ids'=> 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'category.in'            => '医院类型不存在',
            'recommend.in'           => '是否推荐类型不存在',
            'department_ids.integer' => '科室ID必须为整数',
            'page.integer'           => '页码必须为整数',
            'page_num.integer'       => '每页显示条数必须为整数',
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
     *         name="keywords",
     *         in="query",
     *         description="搜索条件【医院名字 详细地址】",
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