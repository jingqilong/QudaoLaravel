<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantService;

class PrimeController extends ApiController
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

    public function login()
    {
        $rules = [
            'mobile'   => ['required','regex:/^1[3456789][0-9]{9}$/'],
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'mobile.required'   => '请输入手机号',
            'mobile.regex'      => '手机号格式有误',
            'password.required' => '请输入密码',
            'password.min'      => '密码最少6位',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantService->login($this->request['mobile'],$this->request['password']);
        if (is_string($res)){
            return ['code' => 100, 'message' => $res];
        }
        return ['code' => 200, 'message' => '登录成功！', 'data' => $res];
    }
}