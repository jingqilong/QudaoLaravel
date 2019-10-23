<?php


namespace App\Api\Middleware;


use Closure;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Tolawho\Loggy\Facades\Loggy;

class SignVerify {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //添加签名开关 by Bardeen
        $env = app()['env'];
        $isSign = Config::get('api.sign_verify');
        //如果设置了开关，
        $info  = $request->all();
        if (isset($isSign[$env])){
            //如果不验，放行！
            if ($isSign[$env] == 0){
                return $next($request);
            }
        }

        //未设置，一律走验签。
        unset($info['s']);
        if(!isset($info['sign']) || empty($info['sign'])){
            return new Response(json_encode(['code' => 100, 'message' => '签名不能为空']));
//            return ['code' => 100, 'message' => '签名不能为空'];
        }
        $osKey = base64_encode(config('api.module_api.signKey'));
        $para_filter = array();
        //去除签名 空格
        foreach($info as $key => $val)
        {
            if($key == "sign")continue;
            else $para_filter[$key] = $info[$key];
        }

        $arg  = "";
        //组装参数
        foreach($para_filter as $key => $val)
        {
            $arg.=$val;
        }
        //如果存在转义字符，那么去掉转义
        $md5Sign = md5($arg.$osKey);
        if((!isset($info['sign'])) || ($md5Sign != $info['sign'])){
            Loggy::write('error','Message:"Signature validation is not passed." URL: '.
                $request->url().'  md5Sign: '.$arg . $osKey . ' sign: '.$md5Sign  . '  RawSign:' .  $info['sign']);
            return new Response(json_encode(['code' => 402, 'message' => '签名验证不通过']));
//            return ['code' => 402, 'message' => '签名验证不通过'];
        }else if($md5Sign == $info['sign']){
            return $next($request);
        }
        Loggy::write('error','Message:"Signature validation is not passed." URL: '.
            $request->url().'  md5Sign: '.$arg . $osKey . ' sign: '.$md5Sign  . '  ' .  $info['sign']);
        return new Response(json_encode(['code' => 402, 'message' => '签名验证不通过']));
//        return ['code' => 402, 'message' => '签名验证不通过'];
    }

    /**
     * 比较版本号
     * @param $current_ver string 当前用户版本号
     * @param $version string 目标版本号
     * @return bool true 当前版本号高于目标版本号
     */
    public function compareVersion($current_ver, $version) {
        //客户端版本
        $clientVersion = str_replace([ 'v', '.' ], '', $current_ver);

        //兼容老版本号v2.1
        $clientVersion = (int)$clientVersion < 100 ? (int)($clientVersion . '0') : (int)$clientVersion;

        return $clientVersion > $version;
    }

}