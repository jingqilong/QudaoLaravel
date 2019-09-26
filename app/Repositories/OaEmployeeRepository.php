<?php


namespace App\Repositories;

use App\Models\OaEmployeeModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OaEmployeeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaEmployeeModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户可以为用户名、邮箱、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['username' => '','password' => '']){

        if (! $token = Auth::guard('oa_api')->attempt($account_password)) {
            return ['code' => 100, 'message' => '账户或密码错误'];
        }

        return $token;
    }


    /**
     * Get the authenticated User.
     *
     * @return mixed
     */
    protected function getUser()
    {
        $auth = Auth::guard('oa_api');
        return $auth->user();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return bool
     */
    protected function logout()
    {
        $auth = Auth::guard('oa_api');
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
        $auth = Auth::guard('oa_api');
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
        $auth = Auth::guard('oa_api');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60
        ];
    }
}
            