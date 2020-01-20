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
        if (!isset($data['feedback_id']) || empty($data['feedback_id'])){
            return false;
        }
        $feedback_id = $data['feedback_id'];//获取详情时缓存的反馈ID
        if (!isset($data['feedback']) || empty($data['feedback'])){
            return false;
        }
        $feedback = $data['feedback'];
        if (!isset($feedback[$feedback_id])){
            return false;
        }
        $feedback_list = $feedback[$feedback_id];
        $this->message_data = $feedback_list;
        Loggy::write('debug','反馈推送：',$feedback_list);
        return json_encode($feedback_list);
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
        if (!isset($data['feedback_id']) || empty($data['feedback_id'])){
            return false;
        }
        $feedback_id = $data['feedback_id'];
        if (!isset($data['feedback']) || empty($data['feedback'])){
            return false;
        }
        $feedback = $data['feedback'];
        if (!isset($feedback[$feedback_id])){
            return false;
        }
        if ($this->message_data == $feedback[$feedback_id]){
            Loggy::write('debug','两次消息日志一至');
            return false;
        }
        Loggy::write('debug','检查通过');
        return true;
    }
}