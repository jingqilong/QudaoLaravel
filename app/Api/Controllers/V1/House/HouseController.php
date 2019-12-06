<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Enums\HouseEnum;
use App\Services\House\DetailsService;
use Illuminate\Support\Facades\Auth;

class HouseController extends ApiController
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
     *     path="/api/v1/house/publish_house",
     *     tags={"房产租赁"},
     *     summary="个人发布房源",
     *     description="sang" ,
     *     operationId="publish_house",
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
     *         description="会员token",
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
     *         description="房屋设施，ID串，例如：【1,2,3,】",
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
    public function publishHouse(){
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
        $member = Auth::guard('member_api')->user();
        $res = $this->detailService->publishHouse($this->request,HouseEnum::PERSON,$member->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/get_my_house_list",
     *     tags={"房产租赁"},
     *     summary="获取我的房源列表",
     *     description="sang" ,
     *     operationId="get_my_house_list",
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
     *         description="会员token",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getMyHouseList(){
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
        $res = $this->detailService->getMyHouseList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/house/get_house_detail",
     *     tags={"房产租赁"},
     *     summary="获取房产详情",
     *     description="sang" ,
     *     operationId="get_house_detail",
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
     *         description="会员token",
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
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getHouseDetail(){
        $rules = [
            'id'   => 'required|integer',
        ];
        $messages = [
            'id.required'  => '房产ID不能为空',
            'id.integer'   => '房产ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->detailService->getHouseDetail($this->request['id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message,'data' => $res];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/house/delete_self_house",
     *     tags={"房产租赁"},
     *     summary="个人删除房源",
     *     description="sang" ,
     *     operationId="delete_self_house",
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
     *         description="会员token",
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
    public function deleteSelfHouse(){
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
        $member = Auth::guard('member_api')->user();
        $res = $this->detailService->deleteHouse($this->request['id'],HouseEnum::PERSON,$member->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/house/edit_self_house",
     *     tags={"房产租赁"},
     *     summary="修改房源",
     *     description="sang" ,
     *     operationId="edit_self_house",
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
     *         description="会员token",
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
        $member = Auth::guard('member_api')->user();
        $res = $this->detailService->editHouse($this->request,HouseEnum::PERSON,$member->id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/house/get_home_list",
     *     tags={"房产租赁"},
     *     summary="获取房产首页列表",
     *     description="sang" ,
     *     operationId="get_home_list",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索字段【房产标题、装修、租赁方式、朝向、类别、小区名称】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="地区代码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="类别，1住宅，2商铺，3写字楼，4厂房/仓库",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rent_range",
     *         in="query",
     *         description="租金范围【200-3000】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rent_order",
     *         in="query",
     *         description="租金排序，1从低到高，2从高到低",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_order",
     *         in="query",
     *         description="面积排序，1从小到大，2从大到小",
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
    public function getHomeList(){
        $rules = [
            'area_code'     => 'integer',
            'category'      => 'integer',
            'rent_range'    => 'regex:/^\d+[-][\d]*$/',
            'rent_order'    => 'in:1,2',
            'area_order'    => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'area_code.integer'         => '地区代码必须为整数',
            'category.integer'          => '类别必须为整数',
            'rent_range.regex'          => '租金范围格式有误',
            'rent_order.in'             => '租金排序取值不在范围内',
            'area_order.in'             => '面积排序取值不在范围内',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->detailService->getHomeList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message, 'data' => $res];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/house/get_house_home_list",
     *     tags={"房产租赁"},
     *     summary="获取房产首页列表  只有首页数据",
     *     description="jing" ,
     *     operationId="get_house_home_list",
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
     *         description="会员token",
     *         required=true,
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
    public function getHouseHomeList(){
        $res = $this->detailService->getHouseHomeList();
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message, 'data' => $res];
    }



    /**
     * @OA\Get(
     *     path="/api/v1/house/get_code_list",
     *     tags={"房产租赁"},
     *     summary="地域选房列表",
     *     description="jing" ,
     *     operationId="get_code_list",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="地区代码",
     *         required=true,
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
    public function getCodeList(){
        $rules = [
            'area_code'     => 'integer',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'area_code.integer'         => '地区代码必须为整数',
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->detailService->getCodeList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->detailService->error];
        }
        return ['code' => 200, 'message' => $this->detailService->message, 'data' => $res];
    }
}