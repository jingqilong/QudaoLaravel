<?php

namespace App\Api\Middleware;

use App\Repositories\OaAdminOperationLogRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRolePermissionsRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class OaPermission extends BaseMiddleware
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
        $method     = $request->method();
        $raw_path   = $request->path();
        $path       = substr($raw_path,strripos($raw_path,"oa") + 2);
        $auth       = Auth::guard('oa_api');
        $user       = $auth->user();
        if (!empty($user->permissions)){
            $permissions_ids = explode(',', $user->permissions);
        }else{
            if (empty($user->role_id)){
                return new Response(json_encode(['code' => 405, 'message' => '无权访问']));
//                return ['code' => 405, 'message' => '无权访问！'];
            }
            $roles_info = OaAdminRolePermissionsRepository::getList(['role_id' => $user->role_id],['permission_id']);
            $permissions_ids = array_column($roles_info,'permission_id');
        }
        if ($permission_list = OaAdminPermissionsRepository::getList(['id' => ['in', $permissions_ids]])){
            foreach ($permission_list as $value){
                if ($value['slug'] == '*'){
                    $this->recordLog($request,$user->id);
                    return $next($request);
                }
                $http_method = explode(',',$value['http_method']);
                $http_path   = explode(',',$value['http_path']);//dd($http_path);
                if (in_array($method, $http_method) && in_array($path, $http_path)){
                    $this->recordLog($request,$user->id);
                    return $next($request);
                }
            }
        }
        return new Response(json_encode(['code' => 405, 'message' => '无权访问']));
//        return ['code' => 405, 'message' => '无权访问！'];
    }

    /**
     * 添加操作日志
     * @param  Request  $request
     * @param $user_id
     */
    public function recordLog($request, $user_id){
        $input = $request->toArray();
        unset($input['sign'],$input['token']);
        $add_log = [
            'user_id'   => $user_id,
            'path'      => $request->path(),
            'method'    => $request->method(),
            'ip'        => $request->getClientIp(),
            'input'     => json_encode($input),
            'created_at'=> date('Y-m-d H:m:s',time())
        ];
        OaAdminOperationLogRepository::getAddId($add_log);
    }
}
