<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SendDingTalkEmail
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     * @desc 使用方法，用event函数：： event(new SendDingTalkEmail($data));
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }
}
