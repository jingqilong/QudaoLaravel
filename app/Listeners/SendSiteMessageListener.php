<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Message\SendService;


class SendSiteMessageListener implements ShouldQueue
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
    public $queue = 'event_message';
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
        app(SendService::class)::sendMessageForEmployee(
            $data['employee_id'],
            $data['category'],
            $data['title'],
            $data['content'],
            $data['relate_id'],
            $data['url'],
            $data['image_ids']
        );
        return false;
    }
}
