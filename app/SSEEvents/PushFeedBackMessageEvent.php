<?php
namespace App\SSEEvent;
use SSEEvent;
use Illuminate\Support\Facades\Cache;
use Tolawho\Loggy\Facades\Loggy;

/**
 * 推送意见反馈消息SSE事件
 * Class PushMessageEvent
 */
class PushFeedBackMessageEvent extends SSEEvent
{
    public $chanel;

    public $message_data;



    /**
     * MessageCountEvent constructor.
     * @param $chanel
     */
    public function __construct($chanel)
    {
        $this->chanel = $chanel;
    }

    /**
     * @return string
     */
    public function update()
    {
        $chanel  = $this->chanel;
        if (!Cache::has($chanel)){
            return false;
        }
        $data = Cache::get($chanel);
        if (!isset($data['feedback']) || empty($data['feedback'])){
            return false;
        }
        $feedback = $data['feedback'];
        $this->message_data = $feedback;
        return json_encode($feedback);
    }

    /**
     * @return bool
     */
    public function check()
    {
        $chanel  = $this->chanel;
        if (!Cache::has($chanel)){
            return false;
        }
        $data = Cache::get($chanel);
        if (!isset($data['feedback']) || empty($data['feedback'])){
            return false;
        }
        $feedback = $data['feedback'];
        if ($this->message_data === $feedback){
            return false;
        }
        return true;
    }
}