<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\DepartmentsService;

class DepartmentsController extends ApiController
{
    public $departmentsServices;


    /**
     * DepartmentsController constructor.
     * @param $departmentsServices
     */
    public function __construct(DepartmentsService $departmentsServices)
    {
        parent::__construct();
        $this->departmentsServices = $departmentsServices;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_departments",
     *     tags={"医疗医院后台"},
     *     summary="添加医疗科室",
     *     description="jing" ,
     *     operationId="add_departments",
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
     *         description="医疗科室标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="医疗科室描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon",
     *         in="query",
     *         description="科室icon",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addDepartments(){
        $rules = [
            'name'         => 'required',
            'icon'         => 'required|integer',
        ];
        $messages = [
            'name.required'        => '医疗科室标题不能为空',
            'icon.required'        => '医疗科室icon不能为空',
            'icon.integer'         => '医疗科室icon不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentsServices->addDepartments($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->departmentsServices->message];
        }
        return ['code' => 100, 'message' => $this->departmentsServices->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/medical/delete_departments",
     *     tags={"医疗医院后台"},
     *     summary="删除医疗科室",
     *     description="jing" ,
     *     operationId="delete_departments",
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
     *         description="医疗科室id",
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
    public function deleteDepartments(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '医疗科室ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentsServices->deleteDepartments($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->departmentsServices->message ,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->departmentsServices->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/medical/edit_departments",
     *     tags={"医疗医院后台"},
     *     summary="修改医疗科室",
     *     description="jing" ,
     *     operationId="edit_departments",
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
     *         description="医疗科室ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="医疗科室标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="医疗科室描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon",
     *         in="query",
     *         description="科室icon",
     *         required=true,
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
    public function editDepartments(){
        $rules = [
            'id'    => 'required|integer',
            'name'  => 'required',
            'icon'  => 'required|integer',
        ];
        $messages = [
            'id.required'    => '医疗科室ID不能为空',
            'id.integer'     => '医疗科室ID必须为整数',
            'name.required'  => '医疗科室标题不能为空',
            'icon.required'  => '医疗科室icon不能为空',
            'icon.integer'   => '医疗科室icon不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->departmentsServices->editDepartments($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->departmentsServices->message];
        }
        return ['code' => 100, 'message' => $this->departmentsServices->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/departments_list",
     *     tags={"医疗医院后台"},
     *     summary="获取医疗科室列表",
     *     description="jing" ,
     *     operationId="departments_list",
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
    public function departmentsList(){
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
        $res = $this->departmentsServices->departmentsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->departmentsServices->error];
        }
        return ['code' => 200, 'message' => $this->departmentsServices->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medical/get_departments_list",
     *     tags={"医疗医院前端"},
     *     summary="获取科室列表",
     *     description="jing" ,
     *     operationId="get_departments_list",
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
     *         description="成员 token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索条件【科室名字】",
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
    public function getDepartmentsList(){
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
        $res = $this->departmentsServices->getDepartmentsList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->departmentsServices->error];
        }
        return ['code' => 200, 'message' => $this->departmentsServices->message,'data' => $res];
    }
}