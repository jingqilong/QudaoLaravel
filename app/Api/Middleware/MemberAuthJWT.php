<?php

namespace App\Api\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class MemberAuthJWT extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = Auth::guard('member_api');
        try {
            if (! $token = $auth->setRequest($request)->getToken()) {
                return ['code' => 401, 'message' => '非法token或token为空'];
            }
            $auth = $auth->setToken($token);
            $user = $auth->user();
        }catch (TokenBlacklistedException $e){
            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }catch (TokenExpiredException $e) {
            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        } catch (JWTException $e) {
            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }

        if (! $user) {
            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }

        return $next($request);
    }
}
