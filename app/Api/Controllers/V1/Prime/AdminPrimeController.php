<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantService;
use App\Services\Prime\ReservationService;
use Illuminate\Support\Facades\Auth;

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
}