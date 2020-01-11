<?php
namespace App\SSEEvent;
use SSEEvent;
use Illuminate\Support\Facades\Cache;
/**
 * 推送消息SSE事件
 * Class PushMessageEvent
 */
class PushMessageEvent extends SSEEvent
{
    /**
     * @return string
     */
    public function update()
    {
        $key = 'push_message';
        if (!Cache::has($key)){
            return false;
        }
        $newMessage = Cache::get($key);
        Cache::forget($key);
//                $id = mt_rand(1, 1000);
//                $newMsgs = [['id' => $id, 'title' => 'title' . $id, 'content' => 'content' . $id]];//get data from database or service.
        if (!empty($newMessage)) {
            return json_encode(['newMessage' => $newMessage]);
        }
        return false;//return false if no new messages
    }

    /**
     * @return bool
     */
    public function check()
    {
        $key = 'push_message';
        if (!Cache::has($key)){
            return false;
        }
        return true;
    }
}