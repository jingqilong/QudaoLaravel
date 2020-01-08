<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\DingTalkEmail;
use Illuminate\Support\Facades\Mail;
use Tolawho\Loggy\Facades\Loggy;

class SendDingTalkEmailListener implements ShouldQueue
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
            $data = $event->data;
            Loggy::write('process','执行了发送邮件事件！接收地址：'.$data['receiver']['receiver_email']);
            $email_data = [
                'event_type'        => $data['event_type'],
                'receiver_email'    => $data['receiver']['receiver_email'],
                'title'             => $data['title'],
                'receiver_name'     => $data['receiver']['receiver_name'],
                'process_full_name' => $data['process_full_name']['process_name'],
                'link_url'          => $data['link_url'],
                'precess_result'    => $data['precess_result'] ?? '',
            ];
            $mailable = new DingTalkEmail($email_data);
            Mail::send($mailable);
        }catch (\Exception $e){
            Loggy::write('process','执行发送邮件事件出错！接收地址：'.$data['receiver']['receiver_email'],json_decode(json_encode($e), true));
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
        Loggy::write('process','发送邮件任务执行失败！',json_decode(json_encode($exception), true));
    }
}
