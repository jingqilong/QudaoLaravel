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
use Tymon\JWTAuth\Facades\JWTAuth;
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
        $auth       = Auth::guard('member_api');
        try {
            if (! $token = $auth->setRequest($request)->getToken()) {
                return new Response(json_encode(['code' => 405, 'message' => '非法token或token为空']));
//                return ['code' => 401, 'message' => '非法token或token为空'];
            }
            if (!$this->checkGuest($request->path())){
                return new Response(json_encode(['code' => 405, 'message' => '权限不足！']));
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
            return new Response(json_encode(['code' => $memberService->code, 'message' => $memberService->error]));
        }

        return $next($request);
    }

    /**
     * 检查访客权限
     * @param $path
     * @return bool
     */
    public function checkGuest($path){
        $payload    = JWTAuth::parseToken()->getPayload();
        if (is_null($aud = $payload->get('aud'))){
            return true;
        }
        if ('guest' == $aud){
            $path  = '/'.$path;
            $greenlight_routes = config('guest.greenlight_routes');
            if (!in_array($path,$greenlight_routes)){
                return false;
            }
        }
        return true;
    }
}
