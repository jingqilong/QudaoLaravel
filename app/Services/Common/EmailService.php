<?php


namespace App\Services\Common;


use App\Enums\EmailEnum;
use App\Mail\CodeEmail;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

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
        $email_ttl      = config('common.email.ttl',300);
        $email_long     = config('common.email.long',4);
        $key            = md5('email_code'.$email.$code_type);
        if (Cache::has($key)){
            $this->setError('验证码已发送，请耐心等待！');
            return false;
        }
        $code = '';
        for ($i=0;$i < $email_long;$i++){
            $code .= rand(0,9);
        }
        $content    = sprintf(EmailEnum::getTemplate($code_type),$code,$email_ttl/60);
        $title      = sprintf(EmailEnum::getTitle($code_type),$code);
        $view       = new CodeEmail($content,$title);
        Mail::to($email)->send($view);
        Cache::add($key,$code,$email_ttl);
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
        if ($code != $local_code){
            $this->setError('验证码不正确！');
            return false;
        }
        Cache::forget($key);
        $this->setMessage('验证通过！');
        return true;
    }
}