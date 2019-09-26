<?php
namespace App\Services\Prime;


use App\Models\PrimeShopsModel;
use App\Repositories\PrimeShopsRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ShopsService extends BaseService
{
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('prime_api');
    }

    /**
     * 用户登录，返回用户信息和TOKEN
     * @param $account
     * @param $password
     * @return mixed|string
     */
    public function login($account, $password){
        //兼容用户名登录、手机号登录、邮箱登录
        $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        $account_type = 'account';
        if (preg_match($mobile_regex, $account)) {
            $account_type = 'phone';
        }

        if (!PrimeShopsRepository::exists([$account_type => $account])){
            return '用户不存在！';
        }
        $token = PrimeShopsRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            return $token['message'];
        }
        $user = $this->auth->user();
        return ['user' => $user, 'token' => $token];
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @param $token
     * @return bool
     */
    public function logout($token)
    {
        if (PrimeShopsRepository::logout($token)){
            return true;
        }
        return false;
    }

    /**
     * Refresh a token.
     *
     * @param $token
     * @return mixed
     */
    public function refresh($token){
        if ($token = PrimeShopsRepository::refresh($token)){
            return $token;
        }
        return false;
    }

    /**
     * Get user info.
     * @return mixed
     */
    public function getUserInfo(){
        if ($user = PrimeShopsRepository::getUser()){
            return $user;
        }
        return false;
    }
}
            