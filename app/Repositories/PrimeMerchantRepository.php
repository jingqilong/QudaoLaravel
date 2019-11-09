<?php


namespace App\Repositories;


use App\Models\PrimeMerchantModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PrimeMerchantRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantModel $model)
    {
        $this->model = $model;
    }


    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['account' => '','password' => '']){
        $where = $account_password;
        unset($where['password']);
        if (!$user = $this->model->where($where)->first()){
            return ['code' => 100, 'message' => '账户不存在'];
        }
        if (!Hash::check($account_password['password'], $user->password)){
            return ['code' => 100, 'message' => '密码不正确'];
        }
        if ($user->disabled != 0){
            return ['code' => 100, 'message' => '您的账户已被禁用，如有疑问，请致电客服！'];
        }
        $auth = Auth::guard('prime_api');
        if (! $token = $auth->fromUser($user)) {
            return ['code' => 100, 'message' => '账户或密码不正确'];
        }
        $auth->setToken($token);
        return $token;
    }


    /**
     * Get the authenticated User.
     *
     * @return mixed
     */
    protected function getUser()
    {
        $auth = Auth::guard('prime_api');
        return $auth->user();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return bool
     */
    protected function logout()
    {
        $auth = Auth::guard('prime_api');
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
        $auth = Auth::guard('prime_api');
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
        $auth = Auth::guard('prime_api');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60
        ];
    }
}
            