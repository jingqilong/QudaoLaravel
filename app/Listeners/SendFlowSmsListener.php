<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Common\SmsService;
use Tolawho\Loggy\Facades\Loggy;

class SendFlowSmsListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * 任务应该发送到的队列的连接的名称
     *
     * @var string|null
     */
    public $connection = 'redis';

    /**
     * 任务应该发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'event_sms';
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
        $data = $event->data;
        Loggy::write('process',date('Y-m-d H:i:s').'执行了发送短信事件！',$event);
        //TODO 这里要处理相关数据
        app(SmsService::class)->sendContent($data['receiver_mobile'],$data['content']);
        return false;
    }
}
