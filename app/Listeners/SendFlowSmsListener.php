<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Common\SmsService;

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
        //TODO 这里要处理相关数据
        app(SmsService::class)->sendContent($data['mobile'],$data['content']);
        return false;
    }
}
