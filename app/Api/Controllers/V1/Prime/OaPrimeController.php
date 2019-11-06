<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantService;
use Illuminate\Http\JsonResponse;

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
     *         description="展示图ID串,不能低于3张",
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
            'mobile'            => 'required|regex:/^1[3456789][0-9]{9}$/',
            'password'          => 'required|string|min:6|max:20',
            'realname'          => 'required',
            'logo_id'           => 'required|integer',
            'type'              => 'required|in:1,2,3',
            'license_img_id'    => 'integer',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'display_img_ids'   => 'required|regex:/^(\d+[,])*\d+$/',
            'describe'          => 'required|max:1000',
            'expect_spend'      => 'regex:/^\-?\d+(\.\d{1,2})?$/',
        ];
        $messages = [
            'name.required'             => '商户名不能为空',
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
            'describe.required'         => '商家描述不能为空',
            'describe.max'              => '商家描述不能超过一千字',
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
}