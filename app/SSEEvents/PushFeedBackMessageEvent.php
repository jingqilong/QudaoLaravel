<?php
namespace App\SSEEvent;
use SSEEvent;
use Illuminate\Support\Facades\Cache;
/**
 * 推送意见反馈消息SSE事件
 * Class PushMessageEvent
 */
class PushFeedBackMessageEvent extends SSEEvent
{
    public $chanel;

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
        $data = Cache::pull($chanel);
        return json_encode($data);
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
        return true;
    }
}