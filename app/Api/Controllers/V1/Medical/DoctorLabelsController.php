<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\DoctorLabelsService;

class DoctorLabelsController extends ApiController
{
    public $DoctorLabelsServices;

    /**
     * DoctorLabelsController constructor.
     * @param $DoctorLabelsServices
     */
    public function __construct(DoctorLabelsService $DoctorLabelsServices)
    {
        parent::__construct();
        $this->DoctorLabelsServices = $DoctorLabelsServices;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_doctorLabels",
     *     tags={"医疗医院后台"},
     *     summary="添加医生标签",
     *     description="jing" ,
     *     operationId="add_doctorLabels",
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
     *         description="医生标签标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addDoctorLabels(){
        $rules = [
            'name'         => 'required',
        ];
        $messages = [
            'name.required'        => '医生标签标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->DoctorLabelsServices->addDoctorLabels($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->DoctorLabelsServices->message];
        }
        return ['code' => 100, 'message' => $this->DoctorLabelsServices->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/medical/delete_doctorLabels",
     *     tags={"医疗医院后台"},
     *     summary="删除医生标签",
     *     description="jing" ,
     *     operationId="delete_doctorLabels",
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
     *         description="标签id",
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
    public function deleteDoctorLabels(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required' => '标签ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->DoctorLabelsServices->deleteDoctorLabels($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->DoctorLabelsServices->message];
        }
        return ['code' => 100, 'message' => $this->DoctorLabelsServices->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/medical/edit_doctorLabels",
     *     tags={"医疗医院后台"},
     *     summary="修改医生标签",
     *     description="jing" ,
     *     operationId="edit_doctorLabels",
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
     *         description="医生标签ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="医生标签标题",
     *         required=true,
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
    public function editDoctorLabels(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
        ];
        $messages = [
            'id.required'           => '医生标签ID不能为空',
            'id.integer'            => '医生标签ID必须为整数',
            'name.required'         => '医生标签标题不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->DoctorLabelsServices->editDoctorLabels($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->DoctorLabelsServices->message];
        }
        return ['code' => 100, 'message' => $this->DoctorLabelsServices->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/doctorLabels_list",
     *     tags={"医疗医院后台"},
     *     summary="获取医生标签列表",
     *     description="jing" ,
     *     operationId="doctorLabels_list",
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
     *         name="keywords",
     *         in="query",
     *         description="关键字搜索【标签名】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="排序方式【默认1正序 2倒叙】",
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
    public function doctorLabelsList(){
        $rules = [
            'sort'          => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'sort.in'          => '页码必须为整数',
            'page.integer'     => '页码必须为整数',
            'page_num.integer' => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->DoctorLabelsServices->getDoctorLabelsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->DoctorLabelsServices->error];
        }
        return ['code' => 200, 'message' => $this->DoctorLabelsServices->message,'data' => $res];
    }
}