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
        $data = $event->data;
        $receiver   = $data['receiver'];
        Loggy::write('process',$receiver['receiver_mobile'].'执行了发送短信事件！');
        $message_data = [
            'receiver_name'     => $receiver['receiver_name'],
            'process_full_name' => $data['process_full_name']['process_name'],
        ];
        $messageTemplate = new MessageTemplate($message_data,$receiver['receiver_iden']);
        app(SmsService::class)->sendContent($receiver['receiver_mobile'],$messageTemplate->getContent());
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
        Loggy::write('process','任务执行失败！');
        //
    }
}
