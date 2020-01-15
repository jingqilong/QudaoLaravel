<?php

namespace App\Services\Common;

use App\Enums\EmailEnum;
use App\Events\SendEmailCode;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

class EmailService extends BaseService
{
    /**
     * 发送邮箱验证码
     * @param $email
     * @param $code_type
     * @return bool
     */
    public function sendCode($email, $code_type){
        if (!EmailEnum::exists($code_type)){
            $this->setError('暂无此类型！');
            return false;
        }
        $key            = md5('email_code'.$email.$code_type);
        if (Cache::has($key)){
            $code_info  = Cache::get($key);
            $time       = time();
            $send_time  = $code_info['time'] + 60;#发送频率为60秒
            if ($send_time > $time){
                $this->setError(($send_time - $time).'秒以后可再次发送验证码！');
                return false;
            }
        }
        $event_data = ['email' => $email,'code_type' => $code_type];
        #异步处理
        event(new SendEmailCode($event_data));
        $this->setMessage('发送成功！');
        return true;
    }

    /**
     * 邮箱验证码验证
     * @param $email
     * @param $code_type
     * @param $code
     * @return bool|string
     */
    public function checkCode($email, $code_type, $code)
    {
        $key        = md5('email_code'.$email.$code_type);
        if (!Cache::has($key)){
            $this->setError('验证码已失效，请重新获取！');
            return false;
        }
        $local_code = Cache::get($key);
        if ($code != $local_code['code']){
            $this->setError('验证码不正确！');
            return false;
        }
        Cache::forget($key);
        $this->setMessage('验证通过！');
        return true;
    }
}