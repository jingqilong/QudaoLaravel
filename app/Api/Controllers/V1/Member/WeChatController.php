<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\MemberService;

class WeChatController extends ApiController
{
    protected $memberService;

    /**
     * WeChatController constructor.
     * @param $memberService
     */
    public function __construct(MemberService $memberService)
    {
        parent::__construct();
        $this->memberService = $memberService;
    }

    /**
     * 微信小程序微信登录接口
     *
     * @OA\Post(
     *     path="/api/v1/member/mini_login",
     *     tags={"会员"},
     *     summary="微信小程序登录",
     *     description="sang" ,
     *     operationId="mini_login",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="code",
     *     description="js_code",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="raw_data",
     *     description="原始数据",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="signature",
     *     description="微信签名",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="encrypted_data",
     *     description="解码数据",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Parameter(
     *     in="query",
     *     name="iv",
     *     description="向量iv",
     *     required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *   @OA\Response(response=100, description="操作成功：union_id,微信用户信息:wx_user_info,系统用户信息:sys_user_info,token"),
     * )
     */
    public function miniLogin(){
        $rules = [
            'code'              => 'required',
            'raw_data'          => 'required',
            'signature'         => 'required',
            'encrypted_data'    => 'required',
            'iv'                => 'required',
        ];
        $messages = [
            'code.required'             => '小程序码不能为空',
            'raw_data.required'         => '原始数据不能为空',
            'signature.required'        => '微信签名不能为空',
            'encrypted_data.required'   => '解码数据不能为空',
            'iv.required'               => '向量iv不能为空',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $result = $this->memberService->miniLogin($this->request);
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $result['message'], 'data' => $result['data']];
        }
        return ['code' => 100, 'message' => $result['message']];
    }


    /**
     * 微信小程序手机绑定接口
     *
     * @OA\Post(
     *     path="/api/v1/member/mini_bind_mobile",
     *     tags={"会员"},
     *     summary="微信小程序登录绑定手机号",
     *     description="sang" ,
     *     operationId="mini_bind_mobile",
     *   @OA\Parameter(in="query",name="sign",description="签名",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="code",description="js_code",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="encrypted_data",description="解码数据",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="iv",description="向量iv",required=true,@OA\Schema(type="string",)),
     *   @OA\Parameter(in="query",name="promo_code",description="推荐码",required=false,@OA\Schema(type="string",)),
     *   @OA\Response(response="default", description="操作成功：返回用户手机号:mobile、用户token")
     * )
     */
    public function miniBindMobile(){
        $rules = [
            'code'              => 'required',
            'encrypted_data'    => 'required',
            'iv'                => 'required',
        ];
        $messages = [
            'code.required'             => '小程序码不能为空',
            'encrypted_data.required'   => '解码数据不能为空',
            'iv.required'               => '向量iv不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        //这个code值是前端访问的网址，返回的。微信返回的openid和session_key以及unionid（unionid不一定返回，openid和session_key肯定会返回把它们两个先存到用户表中）
        $result = $this->memberService->miniBindMobile($this->request);
        if($result['code'] == 1) {
            return ['code' => 200, 'message' => $result['message'], 'data' => $result['data']];
        }
        return ['code' => 100, 'message' => $result['message']];
    }
}