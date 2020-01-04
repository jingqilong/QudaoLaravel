<?php

namespace App\Listeners;

use App\Events\SendFlowSms;
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
     * @param  SendFlowSms  $event
     * @return bool
     */
    public function handle(SendFlowSms $event)
    {
        $data = $event->data;
        Loggy::write('process',date('Y-m-d H:i:s').'执行了发送短信事件！',$event);
        //TODO 这里要处理相关数据
        app(SmsService::class)->sendContent($data['receiver_mobile'],'队列事件测试');
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
        Loggy::write('process','任务执行失败！',$event);
        //
    }
}
