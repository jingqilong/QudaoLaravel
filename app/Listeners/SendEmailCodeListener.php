<?php

namespace App\Listeners;

use App\Enums\EmailEnum;
use App\Mail\CodeEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tolawho\Loggy\Facades\Loggy;

class SendEmailCodeListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return bool
     */
    public function handle($event)
    {
        try {
            $data       = $event->data;
            $email      = $data['email'];
            $code_type  = $data['code_type'];
            $code       = $data['code'];
            $email_ttl  = config('common.email.ttl',300);
            $content    = sprintf(EmailEnum::getTemplate($code_type),$code,$email_ttl/60);
            $title      = sprintf(EmailEnum::getTitle($code_type),$code);
            $view       = new CodeEmail($content,$title);
            Mail::to($email)->send($view);
            Loggy::write('process','执行发送邮件验证码成功！接收地址：'.$email);
        }catch (\Exception $e){
            Loggy::write('process',$e->getMessage());
            Loggy::write('process','执行发送邮件验证码出错！接收地址：'.$email,json_decode(json_encode($e), true));
        }
        return false;
    }

    /**
     * 处理失败任务。
     *
     * @param  object  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed( $event, $exception)
    {
        Loggy::write('process','发送邮件验证码任务执行失败！',json_decode(json_encode($exception), true));
    }
}
