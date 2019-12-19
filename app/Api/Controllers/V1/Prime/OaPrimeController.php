<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantService;

class OaPrimeController extends ApiController
{
    protected $merchantService;

    /**
     * TestApiController constructor.
     * @param MerchantService $merchantService
     */
    public function __construct(MerchantService $merchantService)
    {
        parent::__construct();
        $this->merchantService = $merchantService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/add_merchant",
     *     tags={"精选生活OA后台"},
     *     summary="添加商户",
     *     description="sang" ,
     *     operationId="add_merchant",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="商户名【店名】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="account",
     *         in="query",
     *         description="账户",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="realname",
     *         in="query",
     *         description="店主姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="logo_id",
     *         in="query",
     *         description="商户logo图ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="商户类别，1健身，2餐饮，3宾馆",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="license",
     *         in="query",
     *         description="营业执照号码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="license_img_id",
     *         in="query",
     *         description="营业执照图片ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="地址地区代码【省,市,区,街道,】例如：【310000,310100,310106,310106013】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="log",
     *         in="query",
     *         description="地标经度，例如：【121.48941】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lat",
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
     *         description="详细地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="banner_ids",
     *         in="query",
     *         description="banner图id串",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="display_img_ids",
     *         in="query",
     *         description="展示图ID串,不能低于3张，以3的倍数上传",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="shorttitle",
     *         in="query",
     *         description="短标题，不能超过500字",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="商家描述，不能超过1千字",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="star",
     *         in="query",
     *         description="商家星级,1-5个等级",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="expect_spend",
     *         in="query",
     *         description="预计人均消费，单位：元",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="discount",
     *         in="query",
     *         description="优惠、折扣，例如：满300减50，打五折",
     *         required=false,
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
    public function addMerchant(){
        $rules = [
            'name'              => 'required',
            'account'           => 'required|alpha_dash',
            'log'               => 'required',
            'lat'               => 'required',
            'mobile'            => 'required|regex:/^1[3456789][0-9]{9}$/',
            'password'          => 'required|string|min:6|max:20',
            'realname'          => 'required',
            'logo_id'           => 'required|integer',
            'type'              => 'required|in:1,2,3',
            'license_img_id'    => 'integer',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'display_img_ids'   => 'required|regex:/^(\d+[,])*\d+$/',
            'shorttitle'        => 'required|max:500',
            'describe'          => 'required|max:1000',
            'star'              => 'in:1,2,3,4,5',
            'expect_spend'      => 'regex:/^\-?\d+(\.\d{1,2})?$/',
        ];
        $messages = [
            'name.required'             => '商户名不能为空',
            'account.required'          => '账户不能为空',
            'log.required'              => '经度不能为空',
            'lat.required'              => '纬度不能为空',
            'account.alpha_dash'        => '账户格式有误，可包含字母、数字、破折号、下划线',
            'mobile.required'           => '手机号不能为空',
            'mobile.regex'              => '手机号格式有误',
            'password.required'         => '请输入密码',
            'password.min'              => '密码长度不能低于6位',
            'password.max'              => '密码长度不能超过20位',
            'realname.required'         => '商户真实姓名不能为空',
            'logo_id.required'          => '商户logo不能为空',
            'logo_id.in'                => '商户logoID必须为整数',
            'type.required'             => '商户类别不能为空',
            'type.in'                   => '商户类别不存在',
            'license_img_id.integer'    => '营业执照图片ID必须为整数',
            'banner_ids.required'       => '商户banner图不能为空',
            'banner_ids.regex'          => '商户banner图ID串格式有误',
            'display_img_ids.required'  => '商户展示图不能为空',
            'display_img_ids.regex'     => '商户展示图ID串格式有误',
            'shorttitle.required'       => '短标题不能为空',
            'shorttitle.max'            => '短标题不能超过500字',
            'describe.required'         => '商家描述不能为空',
            'describe.max'              => '商家描述不能超过一千字',
            'star.in'                   => '商家星级不在范围',
            'expect_spend.length'       => '人均消费金额格式有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->addMerchant($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/disabled_merchant",
     *     tags={"精选生活OA后台"},
     *     summary="禁用或启用商户",
     *     description="sang" ,
     *     operationId="disabled_merchant",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="merchant_id",
     *         in="query",
     *         description="商户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function disabledMerchant(){
        $rules = [
            'merchant_id'       => 'required|integer',
        ];
        $messages = [
            'merchant_id.required'  => '商户ID不能为空',
            'merchant_id.integer'   => '商户ID必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->disabledMerchant($this->request['merchant_id']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/is_recommend",
     *     tags={"精选生活OA后台"},
     *     summary="推荐或取消推荐商户",
     *     description="sang" ,
     *     operationId="is_recommend",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="merchant_id",
     *         in="query",
     *         description="商户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_recommend",
     *         in="query",
     *         description="是否推荐，1推荐，2取消推荐",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="操作失败",
     *     ),
     * )
     *
     */
    public function isRecommend(){
        $rules = [
            'merchant_id'       => 'required|integer',
            'is_recommend'      => 'required|in:1,2',
        ];
        $messages = [
            'merchant_id.required'  => '商户ID不能为空',
            'merchant_id.integer'   => '商户ID必须为整数',
            'is_recommend.required' => '是否推荐不能为空',
            'is_recommend.integer'  => '是否推荐取值有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->isRecommend($this->request['merchant_id'],$this->request['is_recommend']);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/prime/edit_merchant",
     *     tags={"精选生活OA后台"},
     *     summary="修改商户",
     *     description="sang" ,
     *     operationId="edit_merchant",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="商户ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="商户名【店名】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="realname",
     *         in="query",
     *         description="店主姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="logo_id",
     *         in="query",
     *         description="商户logo图ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="商户类别，1健身，2餐饮，3宾馆",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="license",
     *         in="query",
     *         description="营业执照号码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="license_img_id",
     *         in="query",
     *         description="营业执照图片ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="地址地区代码【省,市,区,街道,】例如：【310000,310100,310106,310106013】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="详细地址",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="banner_ids",
     *         in="query",
     *         description="banner图id串",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="display_img_ids",
     *         in="query",
     *         description="展示图ID串,不能低于3张，以3的倍数上传",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="shorttitle",
     *         in="query",
     *         description="短标题，不能超过500字",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="describe",
     *         in="query",
     *         description="商家描述，不能超过1千字",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="star",
     *         in="query",
     *         description="商家星级,1-5个等级",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="expect_spend",
     *         in="query",
     *         description="预计人均消费，单位：元",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="discount",
     *         in="query",
     *         description="优惠、折扣，例如：满300减50，打五折",
     *         required=false,
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
    public function editMerchant(){
        $rules = [
            'id'                => 'required|integer',
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[3456789][0-9]{9}$/',
            'realname'          => 'required',
            'logo_id'           => 'required|integer',
            'type'              => 'required|in:1,2,3',
            'license_img_id'    => 'integer',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'display_img_ids'   => 'required|regex:/^(\d+[,])*\d+$/',
            'shorttitle'        => 'required|max:500',
            'describe'          => 'required|max:1000',
            'star'              => 'in:1,2,3,4,5',
            'expect_spend'      => 'regex:/^\-?\d+(\.\d{1,2})?$/',
        ];
        $messages = [
            'id.required'               => '商户ID不能为空',
            'id.integer'                => '商户ID必须为整数',
            'name.required'             => '商户名不能为空',
            'mobile.required'           => '手机号不能为空',
            'mobile.regex'              => '手机号格式有误',
            'realname.required'         => '商户真实姓名不能为空',
            'logo_id.required'          => '商户logo不能为空',
            'logo_id.in'                => '商户logoID必须为整数',
            'type.required'             => '商户类别不能为空',
            'type.in'                   => '商户类别不存在',
            'license_img_id.integer'    => '营业执照图片ID必须为整数',
            'banner_ids.required'       => '商户banner图不能为空',
            'banner_ids.regex'          => '商户banner图ID串格式有误',
            'display_img_ids.required'  => '商户展示图不能为空',
            'display_img_ids.regex'     => '商户展示图ID串格式有误',
            'shorttitle.required'       => '短标题不能为空',
            'shorttitle.max'            => '短标题不能超过500字',
            'describe.required'         => '商家描述不能为空',
            'describe.max'              => '商家描述不能超过一千字',
            'star.in'                   => '商家星级不在范围',
            'expect_spend.length'       => '人均消费金额格式有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->editMerchant($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/prime/merchant_list",
     *     tags={"精选生活OA后台"},
     *     summary="获取商户列表",
     *     description="sang" ,
     *     operationId="merchant_list",
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
     *         description="OA TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索【商户名，手机号，真实姓名，营业执照号码】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="商户类别，1健身，2餐饮，3宾馆",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="地址地区代码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="disabled",
     *         in="query",
     *         description="是否禁用，1已启用，2已禁用",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
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
    public function merchantList()
    {
        $rules = [
            'keywords'      => 'string',
            'type'          => 'in:1,2,3',
            'area_code'     => 'integer',
            'disabled'      => 'in:1,2',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'type.in'                   => '商户类别不存在',
            'area_code.integer'         => '地址地区代码必须为数字',
            'disabled.in'               => '是否禁用取值有误',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->merchantList($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message, 'data' => $res];
    }
}