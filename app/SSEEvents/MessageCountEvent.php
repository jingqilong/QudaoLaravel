<?php
namespace App\SSEEvent;
use SSEEvent;
use Illuminate\Support\Facades\Cache;
/**
 * 推送消息未读数量SSE事件
 * Class PushMessageEvent
 */
class MessageCountEvent extends SSEEvent
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
        $index  = $this->chanel;
        $key    = config('message.cache-key');
        if (!Cache::has($key)){
            return false;
        }
        $all_count = Cache::get($key);
        $count = $all_count[$index] ?? 0;
        $this->message_data = $count;
        return json_encode(['count' => $count]);
    }

    /**
     * @return bool
     */
    public function check()
    {
        $index  = $this->chanel;
        $key    = config('message.cache-key');
        if (!Cache::has($key)){
            return false;
        }
        $all_count = Cache::get($key);
        $count = $all_count[$index] ?? 0;
        if (empty($count)){
            return false;
        }
        if($count == $this->message_data){
            return false;
        }
        return true;
    }
}