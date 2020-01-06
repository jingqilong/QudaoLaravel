<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Tolawho\Loggy\Facades\Loggy;

class SendFlowSms
//    implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     * @desc 使用方法，用event函数：： event(new SendFlowSms($data));
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }
//
//    /**
//     * Get the data to broadcast.
//     *
//     * @return array
//     */
//    public function broadcastWith()
//    {
//        return $this->data;
//    }
//
//    /**
//     * Get the channels the event should broadcast on.
//     *
//     * @return \Illuminate\Broadcasting\Channel|array
//     */
//    public function broadcastOn()
//    {
//        Loggy::write('process','执行了broadcastOn()！');
//        return new PrivateChannel('event_sms');
//    }
}
