<?php


namespace App\Repositories;


use App\Models\PrimeShopsModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class PrimeShopsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeShopsModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户可以为用户名、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['account' => '','password' => '']){
        $where = $account_password;
        unset($where['password']);
        if (!$user = $this->model->where($where)->first()){
            return ['code' => 100, 'message' => '账户不存在'];
        }
        if (!Hash::check($account_password['password'],$user->pwd)){
            return ['code' => 100, 'message' => '密码不正确'];
        }

        if (! $token = Auth::guard('prime_api')->fromUser($user)) {
            return ['code' => 100, 'message' => '账户或密码不正确'];
        }
        Auth::guard('prime_api')->setToken($token);
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
            