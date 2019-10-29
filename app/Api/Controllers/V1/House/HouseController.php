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
     *         description="地址地区代码，例如：【310000,310100,310106,310106013,】",
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
     *         name="leasing_id",
     *         in="query",
     *         description="租赁方式ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         description="楼层，【楼层 | 总楼层】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit_id",
     *         in="query",
     *         description="户型ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         name="toward_id",
     *         in="query",
     *         description="朝向ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
            'leasing_id'        => 'required|integer',
            'decoration'        => 'required|in:1,2,3',
            'height'            => 'required|regex:/^\-?\d+(\.\d{1,2})?$/',
            'area'              => 'required|integer',
            'image_ids'         => 'required|regex:/^(\d+[,])*\d+$/',
            'storey'            => [
                'required',
                'regex:/^[\S]+[\/]+[\d+]*$/'],
            'unit_id'           => 'required|integer',
            'toward_id'         => 'required|integer',
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
            'leasing_id.required'   => '租赁方式不能为空',
            'leasing_id.integer'    => '租赁方式ID必须为整数',
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
            'unit_id.required'      => '户型不能为空',
            'unit_id.integer'       => '户型ID必须为整数',
            'toward_id.required'    => '朝向不能为空',
            'toward_id.integer'     => '朝向ID必须为整数',
            'category.required'     => '房产类别不能为空',
            'category.in'           => '房产类别不存在',
            'facilities_ids.regex'  => '房屋设施格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $member = Auth::guard('member_api')->user();
        $res = $this->detailService->publishHouse($this->request,HouseEnum::PERSON,$member->m_id);
        if ($res){
            return ['code' => 200, 'message' => $this->detailService->message];
        }
        return ['code' => 100, 'message' => $this->detailService->error];
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
}