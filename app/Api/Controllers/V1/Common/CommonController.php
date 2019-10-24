<?php


namespace App\Api\Controllers\V1\Common;

use App\Api\Controllers\ApiController;
use App\Services\Common\HomeService;
use App\Services\Common\SmsService;
use App\Services\Member\MemberService;

class CommonController extends ApiController
{
    public $smsService;
    public $memberService;
    public $homeService;

    /**
     * QiNiuController constructor.
     * @param SmsService $smsService
     * @param MemberService $memberService
     * @param HomeService $homeService
     */
    public function __construct(SmsService $smsService,
                                MemberService $memberService,
                                HomeService $homeService)
    {
        parent::__construct();
        $this->smsService       = $smsService;
        $this->memberService    = $memberService;
        $this->homeService      = $homeService;
        if (isset($this->request['token'])){
            $this->middleware('member.jwt.auth');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/send_captcha",
     *     tags={"公共"},
     *     summary="发送短信验证码",
     *     description="sang" ,
     *     operationId="send_captcha",
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
     *         name="type",
     *         in="query",
     *         description="短信类型【0，默认类型1、会员模块登录,2、修改密码,3、成员短信注册，4、成员绑定手机号,....】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="发送失败",
     *     ),
     * )
     *
     */
    public function sendCaptcha(){
        $rules = [
            'type'      => 'required|integer',
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            'type.required'     => '请输入短信类型',
            'type.integer'      => '短信类型必须为整数',
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->smsService->sendCode($this->request['mobile'], $this->request['type']);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message']];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/common/mobile_exists",
     *     tags={"公共"},
     *     summary="检测成员手机号是否注册",
     *     description="sang" ,
     *     operationId="mobile_exists",
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
     *         name="mobile",
     *         in="query",
     *         description="手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="查询成功！",
     *     ),
     * )
     *
     */
    public function mobileExists(){
        $rules = [
            'mobile'    => 'required|regex:/^1[3456789][0-9]{9}$/',
        ];
        $messages = [
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberService->mobileExists($this->request['mobile']);
        return ['code' => 200, 'message' => $this->memberService->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/home",
     *     tags={"公共"},
     *     summary="获取首页",
     *     description="sang" ,
     *     operationId="home",
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
     *         description="会员token（非必填）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function home(){
        $res = $this->homeService->getHome();
        if ($res === false){
            return ['code' => 100, 'message' => $this->homeService->error];
        }
        return ['code' => 200, 'message' => $this->homeService->message,'data' => $res];
    }
}