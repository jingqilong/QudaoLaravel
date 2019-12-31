<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\PublicService;
use Illuminate\Http\JsonResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicController extends ApiController
{
    protected $publicService;

    /**
     * TestApiController constructor.
     * @param PublicService $publicService
     */
    public function __construct(PublicService $publicService)
    {
        parent::__construct();
        $this->publicService = $publicService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/promote_qr_code",
     *     tags={"会员"},
     *     summary="获取推广二维码",
     *     description="获取到可以跳转小程序的二维码推广图片,sang",
     *     operationId="promote_qr_code",
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
     *         description="用户token",
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
    public function promoteQrCode()
    {
        $res = $this->publicService->getQrCode();
        if (!$res){
            return ['code' => 100, 'message' => $this->publicService->error];
        }
        return ['code' => 200, 'message' => $this->publicService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_test_qr_code",
     *     tags={"会员"},
     *     summary="获取测试二维码",
     *     description="用于给外部人员测试使用,sang",
     *     operationId="get_test_qr_code",
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
     *         description="用户token",
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
    public function getTestQrCode()
    {
        $res = $this->publicService->getTestQrCode();
        if (!$res){
            return ['code' => 100, 'message' => $this->publicService->error];
        }
        return ['code' => 200, 'message' => $this->publicService->message, 'data' => $res];
    }
}