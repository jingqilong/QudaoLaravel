<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Enums\HouseEnum;
use App\Services\House\DetailsService;
use Illuminate\Support\Facades\Auth;

class OaHouseController extends ApiController
{
    public $detailService;

    /**
     * HouseController constructor.
     * @param $detailService
     */
    public function __construct(DetailsService $detailService)
    {
        parent::__construct();
        $this->detailService = $detailService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/add_house",
     *     tags={"房产租赁后台"},
     *     summary="添加房源",
     *     description="sang" ,
     *     operationId="add_house",
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
     *         description="房产标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
     *         name="describe",
     *         in="query",
     *         description="房产描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rent",
     *         in="query",
     *         description="租金，例如3900，或1999.99",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tenancy",
     *         in="query",
     *         description="租期，1、小时，2、天，3、周，4、月，5、年",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="leasing",
     *         in="query",
     *         description="租赁方式，例如：付一押一",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="decoration",
     *         in="query",
     *         description="装修，1普装，2精装，3豪装",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="height",
     *         in="query",
     *         description="楼层高度，单位：米",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area",
     *         in="query",
     *         description="总面积，单位：平米㎡",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="房产照片ID串，例如：【1,2,21】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="storey",
     *         in="query",
     *         description="楼层，【楼层 / 总楼层】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit",
     *         in="query",
     *         description="户型，例如：三室一厅一卫",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="condo_name",
     *         in="query",
     *         description="小区名称",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="toward",
     *         in="query",
     *         description="朝向，例如：朝南",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="类别，1住宅，2商铺，3写字楼，4厂房/仓库...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="facilities_ids",
     *         in="query",
     *         description="房屋设施，ID串，例如：【1,2,3】",
     *         required=true,
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
    public function addHouse(){
        $rules = [
            'title'             => 'required',
            'area_code'         => 'required|regex:/^(\d+[,])*\d+$/',
            'address'           => 'required',
            'rent'              => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'tenancy'           => 'required|in:1,2,3,4,5',
            'leasing'           => 'required',
            'decoration'        => 'required|in:1,2,3',
            'height'            => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'area'              => 'required|integer',
            'image_ids'         => 'required|regex:/^(\d+[,])*\d+$/',
            'storey'            => [
                'required',
                'regex:/^[\S]+[\/]+[\d+]*$/'],
            'unit'              => 'required',
            'toward'            => 'required',
            'category'          => 'required|in:1,2,3,4',
            'facilities_ids'    => 'regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'title.required'        => '房产标题不能为空',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'address.required'      => '详细地址不能为空',
            'rent.required'         => '租金不能为空',
            'rent.regex'            => '租金格式有误',
            'tenancy.required'      => '租期不能为空',
            'tenancy.in'            => '租期不存在',
            'leasing.required'      => '租赁方式不能为空',
            'decoration.required'   => '装修类型不能为空',
            'decoration.in'         => '装修类型不存在',
            'height.required'       => '楼层高度不能为空',
            'height.regex'          => '楼层高度格式有误',
            'area.required'         => '总面积不能为空',
            'area.integer'          => '总面积必须为整数',
            'image_ids.required'    => '房产详情图不能为空',
            'image_ids.regex'       => '房产详情图格式有误',
            'storey.required'       => '楼层不能为空',
            'storey.regex'          => '楼层格式有误，示例【B1|20】',
            'unit.required'         => '户型不能为空',
            'toward.required'       => '朝向不能为空',
            'category.required'     => '房产类别不能为空',
            'category.in'           => '房产类别不存在',
            'facilities_ids.regex'  => '房屋设施格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $oa = Auth::guard('oa_api')->user();
        $res = $this->detailService->publishHouse($this->request,HouseEnum::PLATFORM,$oa->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_house",
     *     tags={"房产租赁后台"},
     *     summary="删除房源",
     *     description="sang" ,
     *     operationId="delete_house",
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
     *         description="房产ID",
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
    public function deleteHouse(){
        $rules = [
            'id'        => 'required|integer',
        ];
        $messages = [
            'id.required'           => '房源ID不能为空',
            'id.integer'            => '房源ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $oa = Auth::guard('oa_api')->user();
        $res = $this->detailService->deleteHouse($this->request['id'],HouseEnum::PLATFORM,$oa->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_house",
     *     tags={"房产租赁后台"},
     *     summary="修改房源",
     *     description="sang" ,
     *     operationId="edit_house",
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
     *         description="房产ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="房产标题",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
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
     *         name="describe",
     *         in="query",
     *         description="房产描述",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rent",
     *         in="query",
     *         description="租金，例如3900，或1999.99",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tenancy",
     *         in="query",
     *         description="租期，1、小时，2、天，3、周，4、月，5、年",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="leasing",
     *         in="query",
     *         description="租赁方式，例如：付一押一",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="decoration",
     *         in="query",
     *         description="装修，1普装，2精装，3豪装",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="height",
     *         in="query",
     *         description="楼层高度，单位：米",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area",
     *         in="query",
     *         description="总面积，单位：平米㎡",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="image_ids",
     *         in="query",
     *         description="房产照片ID串，例如：【1,2,21】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="storey",
     *         in="query",
     *         description="楼层，【楼层 / 总楼层】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit",
     *         in="query",
     *         description="户型，例如：三室一厅一卫",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="condo_name",
     *         in="query",
     *         description="小区名称",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="toward",
     *         in="query",
     *         description="朝向，例如：朝南",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="类别，1住宅，2商铺，3写字楼，4厂房/仓库...",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="facilities_ids",
     *         in="query",
     *         description="房屋设施，ID串，例如：【1,2,3】",
     *         required=true,
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
    public function editHouse(){
        $rules = [
            'id'                => 'required|integer',
            'title'             => 'required',
            'area_code'         => 'required|regex:/^(\d+[,])*\d+$/',
            'address'           => 'required',
            'rent'              => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'tenancy'           => 'required|in:1,2,3,4,5',
            'leasing'           => 'required',
            'decoration'        => 'required|in:1,2,3',
            'height'            => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'area'              => 'required|integer',
            'image_ids'         => 'required|regex:/^(\d+[,])*\d+$/',
            'storey'            => [
                'required',
                'regex:/^[\S]+[\/]+[\d+]*$/'],
            'unit'              => 'required',
            'toward'            => 'required',
            'category'          => 'required|in:1,2,3,4',
            'facilities_ids'    => 'regex:/^(\d+[,])*\d+$/',
        ];
        $messages = [
            'id.required'           => '房产ID不能为空',
            'id.integer'            => '房产ID必须为整数',
            'title.required'        => '房产标题不能为空',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'address.required'      => '详细地址不能为空',
            'rent.required'         => '租金不能为空',
            'rent.regex'            => '租金格式有误',
            'tenancy.required'      => '租期不能为空',
            'tenancy.in'            => '租期不存在',
            'leasing.required'      => '租赁方式不能为空',
            'decoration.required'   => '装修类型不能为空',
            'decoration.in'         => '装修类型不存在',
            'height.required'       => '楼层高度不能为空',
            'height.regex'          => '楼层高度格式有误',
            'area.required'         => '总面积不能为空',
            'area.integer'          => '总面积必须为整数',
            'image_ids.required'    => '房产详情图不能为空',
            'image_ids.regex'       => '房产详情图格式有误',
            'storey.required'       => '楼层不能为空',
            'storey.regex'          => '楼层格式有误，示例【B1|20】',
            'unit.required'         => '户型不能为空',
            'toward.required'       => '朝向不能为空',
            'category.required'     => '房产类别不能为空',
            'category.in'           => '房产类别不存在',
            'facilities_ids.regex'  => '房屋设施格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $oa = Auth::guard('oa_api')->user();
        $res = $this->detailService->editHouse($this->request,HouseEnum::PLATFORM,$oa->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/house_list",
     *     tags={"房产租赁后台"},
     *     summary="获取房产列表",
     *     description="sang" ,
     *     operationId="house_list",
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
     *         name="keywords",
     *         in="query",
     *         description="搜索字段【房产标题、详细地址、租期、租赁方式、朝向、类别、小区名称】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="decoration",
     *         in="query",
     *         description="装修，默认1普装，2精装，3豪装",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="publisher",
     *         in="query",
     *         description="发布方，默认1个人，2平台",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态，0待审核，1通过，2未通过，3已出租",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function houseList(){
        $rules = [
            'decoration'    => 'integer',
            'publisher'     => 'integer',
            'status'        => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'decoration.integer'        => '装修类型必须为整数',
            'publisher.integer'         => '发布方必须为整数',
            'status.integer'            => '状态必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->detailService->houseList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message, 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/house/audit_house",
     *     tags={"房产租赁后台"},
     *     summary="审核房源",
     *     description="sang" ,
     *     operationId="audit_house",
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
     *         description="OA_token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="房源ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="audit",
     *         in="query",
     *         description="审核结果，1通过，2驳回",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="审核失败",
     *     ),
     * )
     *
     */
    public function auditHouse(){
        $rules = [
            'id'            => 'required|integer',
            'audit'         => 'required|in:1,2',
        ];
        $messages = [
            'id.required'               => '房源ID不能为空',
            'id.integer'                => '房源ID必须为整数',
            'audit.required'            => '审核结果不能为空',
            'audit.in'                  => '审核结果取值有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->detailService->auditHouse($this->request['id'],$this->request['audit']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message];
    }
}