<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\DoctorsService;

class DoctorsController extends ApiController
{
    public $doctorsService;

    /**
     * DoctorsController constructor.
     * @param $doctorsService
     */
    public function __construct(DoctorsService $doctorsService)
    {
        parent::__construct();
        $this->doctorsService = $doctorsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/medical/add_doctors",
     *     tags={"医疗医院后台"},
     *     summary="添加医生",
     *     description="jing" ,
     *     operationId="add_doctors",
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
     *         description="医疗医生姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="医生性别[1 男 2女]",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="职称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="good_at",
     *         in="query",
     *         description="擅长介绍",
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
     *             type="string",
     *         )
     *  ),
     *     @OA\Parameter(
     *         name="recommend",
     *         in="query",
     *         description="推荐[0 不推荐 1推荐]",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hospitals_id",
     *         in="query",
     *         description="医院ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="department_id",
     *         in="query",
     *         description="科室ID",
     *         required=false,
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
    public function addDoctors(){
        $rules = [
            'title'         => 'required|string',
            'name'          => 'required|string',
            'sex'           => 'required|integer',
            'good_at'       => 'required|string',
            'introduction'  => 'string',
            'recommend'     => 'required|integer',
            'hospitals_id'  => 'required|integer',
            'department_id' => 'required|integer',
        ];
        $messages = [
            'name.required'             => '医生姓名不能为空',
            'name.string'               => '医生姓名必须为字符串',
            'title.string'              => '医生职称必须为字符串',
            'title.required'            => '医生职称不能为空',
            'sex.required'              => '医生性别不能为空',
            'sex.integer'               => '医生姓名不是整数',
            'good_at.string'            => '擅长介绍必须为字符串',
            'good_at.required'          => '擅长介绍不能为空',
            'introduction.string'       => '简介必须为字符串',
            'hospitals_id.integer'      => '医院ID不是整数',
            'hospitals_id.required'     => '医院ID不能为空',
            'department_id.integer'     => '科室ID不是整数',
            'department_id.required'    => '科室ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->doctorsService->addDoctors($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->doctorsService->message];
        }
        return ['code' => 100, 'message' => $this->doctorsService->error];
    }
}