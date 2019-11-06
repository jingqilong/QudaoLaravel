<?php
namespace App\Services\Prime;


use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MerchantService extends BaseService
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
     * @param $mobile
     * @param $password
     * @return mixed|string
     */
    public function login($mobile, $password){

        if (!PrimeMerchantRepository::exists(['mobile' => $mobile])){
            return '用户不存在！';
        }
        $token = PrimeMerchantRepository::login($mobile,$password);
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
        if (PrimeMerchantRepository::logout($token)){
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
        if ($token = PrimeMerchantRepository::refresh($token)){
            return $token;
        }
        return false;
    }

    /**
     * Get user info.
     * @return mixed
     */
    public function getUserInfo(){
        if ($user = PrimeMerchantRepository::getUser()){
            return $user;
        }
        return false;
    }

    /**
     * 添加商户
     * @param $request
     * @return bool
     */
    public function addMerchant($request)
    {
        if (PrimeMerchantRepository::exists(['mobile' => $request['mobile']])){
            $this->setError('手机号已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['name' => $request['name']])){
            $this->setError('该商户名已被使用');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'realname'      => $request['realname'],
            'logo_id'       => $request['logo_id'],
            'type'          => $request['type'],
            'license'       => $request['license'] ?? '',
            'license_img_id' => $request['license_img_id'] ?? '',
            'area_code'     => $request['area_code'] ?? '',
            'address'       => $request['address'] ?? '',
            'banner_ids'    => $request['banner_ids'],
            'display_img_ids' => $request['display_img_ids'],
            'describe'      => $request['describe'],
            'expect_spend'  => $request['expect_spend'] ?? '',
            'discount'      => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists($add_arr)){
            $this->setError('该商户已添加');
            return false;
        }
        $add_arr['password']    = Hash::make($request['password']);
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (PrimeMerchantRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return true;
    }
}
            