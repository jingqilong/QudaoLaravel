<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantService;
use App\Services\Prime\ReservationService;

class AdminPrimeController extends ApiController
{
    protected $merchantService;
    protected $reservationService;

    /**
     * TestApiController constructor.
     * @param MerchantService $merchantService
     * @param ReservationService $reservationService
     */
    public function __construct(MerchantService $merchantService,ReservationService $reservationService)
    {
        parent::__construct();
        $this->merchantService      = $merchantService;
        $this->reservationService   = $reservationService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/login",
     *     tags={"精选生活商户后台"},
     *     summary="登录",
     *     description="sang" ,
     *     operationId="login",
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
     *         name="account",
     *         in="query",
     *         description="账户，账户、手机号登录",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="登录失败",
     *     ),
     * )
     *
     */
    public function login()
    {
        $rules = [
            'account'   => 'required',
            'password'  => 'required|string|min:6',
        ];
        $messages = [
            'account.required'  => '请输入账户',
            'password.required' => '请输入密码',
            'password.min'      => '密码最少6位',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->login($this->request['account'],$this->request['password']);
        if (is_string($res)){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '登录成功！', 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/logout",
     *     tags={"精选生活商户后台"},
     *     summary="退出登录",
     *     description="sang" ,
     *     operationId="logout",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="退出登录失败",
     *     ),
     * )
     *
     */
    public function logout()
    {
        if ($this->merchantService->logout($this->request['token'])){
            return ['code' => 200, 'message' => '退出成功！'];
        }
        return ['code' => 100, 'message' => '退出失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/refresh",
     *     tags={"精选生活商户后台"},
     *     summary="刷新TOKEN",
     *     description="sang" ,
     *     operationId="refresh",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="token刷新失败",
     *     ),
     * )
     *
     */
    public function refresh()
    {
        if ($token = $this->merchantService->refresh($this->request['token'])){
            return ['code' => 200, 'message' => '刷新成功！', 'data' => ['token' => $token]];
        }
        return ['code' => 100, 'message' => '刷新失败！'];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/prime/admin/get_user_info",
     *     tags={"精选生活商户后台"},
     *     summary="获取用户信息",
     *     description="sang" ,
     *     operationId="get_user_info",
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
     *         description="用户TOKEN",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="用户信息获取失败",
     *     ),
     * )
     *
     */
    public function getUserInfo()
    {
        if ($user = $this->merchantService->getUserInfo()){
            return ['code' => 200, 'message' => '用户信息获取成功！', 'data' => ['user' => $user]];
        }
        return ['code' => 100, 'message' => '用户信息获取失败！'];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prime/admin/edit_merchant",
     *     tags={"精选生活商户后台"},
     *     summary="修改个人信息",
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
     *         description="商户 TOKEN",
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
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editMerchant(){
        $rules = [
            'name'              => 'required',
            'mobile'            => 'required|regex:/^1[3456789][0-9]{9}$/',
            'realname'          => 'required',
            'logo_id'           => 'required|integer',
            'banner_ids'        => 'required|regex:/^(\d+[,])*\d+$/',
            'display_img_ids'   => 'required|regex:/^(\d+[,])*\d+$/',
            'shorttitle'        => 'required|max:500',
            'describe'          => 'required|max:1000',
            'expect_spend'      => 'regex:/^\-?\d+(\.\d{1,2})?$/',
        ];
        $messages = [
            'name.required'             => '商户名不能为空',
            'mobile.required'           => '手机号不能为空',
            'mobile.regex'              => '手机号格式有误',
            'realname.required'         => '商户真实姓名不能为空',
            'logo_id.required'          => '商户logo不能为空',
            'logo_id.in'                => '商户logoID必须为整数',
            'banner_ids.required'       => '商户banner图不能为空',
            'banner_ids.regex'          => '商户banner图ID串格式有误',
            'display_img_ids.required'  => '商户展示图不能为空',
            'display_img_ids.regex'     => '商户展示图ID串格式有误',
            'shorttitle.required'       => '短标题不能为空',
            'shorttitle.max'            => '短标题不能超过500字',
            'describe.required'         => '商家描述不能为空',
            'describe.max'              => '商家描述不能超过一千字',
            'expect_spend.length'       => '人均消费金额格式有误',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->userEditMerchant($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->merchantService->error];
        }
        return ['code' => 200, 'message' => $this->merchantService->message];
    }
}