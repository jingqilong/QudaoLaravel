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
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="跳转链接",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="二维码大小，单位：px",
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
    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse|string
     */
    public function promoteQrCode()
    {
        $rules = [
            'url'       => 'required|url',
            'size'      => 'required|integer',
        ];
        $messages = [
            'url.required'          => '跳转链接不能为空',
            'url.url'               => '跳转链接格式有误',
            'size.required'         => '二维码大小不能为空',
            'size.url'              => '二维码大小必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->publicService->getQrCode($this->request['url'],$this->request['size']);
        if (!$res){
            return ['code' => 100, 'message' => $this->publicService->error];
        }
        return ['code' => 200, 'message' => $this->publicService->message, 'data' => $res];
    }
}