<?php

namespace App\Listeners;

use App\Events\SendFlowSms;
use App\Services\Message\MessageTemplate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Common\SmsService;
use Tolawho\Loggy\Facades\Loggy;

class SendFlowSmsListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  SendFlowSms  $event
     * @return bool
     */
    public function handle(SendFlowSms $event)
    {
        try {
            $data = $event->data;
            $receiver   = $data['receiver'];
            if (!isset($receiver['receiver_mobile'])){
                Loggy::write('process','短信发送失败！原因：无手机号');
                return false;
            }
            Loggy::write('process','执行了发送短信事件！手机号：'.$receiver['receiver_mobile']);
            $message_data = [
                'receiver_name'     => $receiver['receiver_name'],
                'process_full_name' => $data['process_full_name']['process_name'],
            ];
            $messageTemplate = new MessageTemplate($message_data,$receiver['receiver_iden']);
            app(SmsService::class)->sendContent($receiver['receiver_mobile'],$messageTemplate->getContent());
        }catch (\Exception $e){
            Loggy::write('process','执行发送短信事件出错！手机号：'.$receiver['receiver_mobile'],json_decode(json_encode($e), true));
        }
        return false;
    }

    /**
     * 处理失败任务。
     *
     * @param  SendFlowSms  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(SendFlowSms $event, $exception)
    {
        Loggy::write('process','发送短信任务执行失败！',json_decode(json_encode($exception), true));
    }
}
