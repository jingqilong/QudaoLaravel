<?php

namespace App\Api\Middleware;

use App\Services\Member\MemberService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
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
                return new Response(json_encode(['code' => 401, 'message' => '非法token或token为空']));
//                return ['code' => 401, 'message' => '非法token或token为空'];
            }
            $auth = $auth->setToken($token);
            $user = $auth->user();
        }catch (TokenBlacklistedException $e){
            return new Response(json_encode(['code' => 401, 'message' => '登录失效，请重新登录']));
//            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }catch (TokenExpiredException $e) {
            return new Response(json_encode(['code' => 401, 'message' => '登录失效，请重新登录']));
//            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        } catch (JWTException $e) {
            return new Response(json_encode(['code' => 401, 'message' => '登录失效，请重新登录']));
//            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }

        if (! $user) {
            return new Response(json_encode(['code' => 401, 'message' => '登录失效，请重新登录']));
//            return ['code' => 401, 'message' => '登录失效，请重新登录'];
        }

        //测试用户检查,如果测试时间已过，则登录失败
        $memberService = new MemberService();
        if (false == $memberService->checkTestUser($user->id)){
            return new Response(json_encode(['code' => 100, 'message' => $memberService->error]));
        }

        return $next($request);
    }
}
