<?php


namespace App\Repositories;


use App\Models\MemberModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加新用户
     * @param $mobile
     * @param array $sub_data
     * @return mixed
     */
    protected function addUser($mobile, $sub_data = [])
    {
        $user_data = [
            'm_num' => $mobile,
            'm_phone' => $mobile,
            'm_time' => date('y-m-d h:m:s',time()),
            'm_referral_code' => $this->getReferralCode()
        ];
        $user_data = array_merge($user_data,$sub_data);
        return $this->getAddId($user_data);
    }

    /**
     * 获取token
     * @param mixed $user 用户模型获取id
     * @return mixed
     */
    protected function getToken($user)
    {
        if (is_integer($user)){
            $user = $this->model->where(['m_id' => $user])->first();
        }
        if (! $token = Auth::guard('member_api')->fromUser($user)) {
            return false;
        }
        Auth::guard('member_api')->setToken($token);
        return $token;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户可以为会员卡号、邮箱、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['m_num' => '','password' => '']){
        $where = $account_password;
        unset($where['password']);
        if (!$user = $this->model->where($where)->first()){
            return ['code' => 100, 'message' => '账户不存在'];
        }
        if (!Hash::check($account_password['password'],$user->m_password)){
            return ['code' => 100, 'message' => '密码不正确'];
        }

        if (! $token = Auth::guard('prime_api')->fromUser($user)) {
            return ['code' => 100, 'message' => 'token获取失败'];
        }
        Auth::guard('member_api')->setToken($token);
        return $token;
    }


    /**
     * Get the authenticated User.
     *
     * @return mixed
     */
    protected function getUser()
    {
        $auth = Auth::guard('member_api');
        return $auth->user();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return bool
     */
    protected function logout()
    {
        $auth = Auth::guard('member_api');
        $auth->logout();

        return true;
    }

    /**
     * Refresh a token.
     *
     * @return mixed
     */
    protected function refresh()
    {
        $auth = Auth::guard('member_api');
        return $auth->refresh();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return mixed
     */
    protected function respondWithToken($token)
    {
        $auth = Auth::guard('member_api');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60
        ];
    }

    /**
     * 生成邀请码
     * @param int $len
     * @return string
     */
    protected function getReferralCode($len = 8){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        if ($this->exists(['m_referral_code' => $str])){
            return self::getReferralCode($len);
        }
        return $str;
    }
}
            