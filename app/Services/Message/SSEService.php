<?php

namespace App\Services\Message;

use App\Services\BaseService;
use App\SSEEvent\MessageCountEvent;
use App\SSEEvent\PushFeedBackMessageEvent;
use SSE;
use Tolawho\Loggy\Facades\Loggy;

class SSEService extends BaseService
{

    /**
     * @desc 推送消息未读数量
     * @param string $chanel    通道
     */
    public function pushMessageUnreadCount($chanel){
        try {
            $sse = new SSE();
            $sse->sleep_time = 5;   #数据发送后睡眠时间
            $sse->exec_limit = 10;  #脚本时间限制
            $sse->allow_cors = true;
            $sse->addEventListener('message', new MessageCountEvent($chanel));
            $sse->addEventListener('feed_back_message', new PushFeedBackMessageEvent($chanel));
            $sse->start();
        } catch (\Exception $e) {
            Loggy::write('error',$e->getMessage(),$e);
        }
    }
//
//    /**
//     * @desc 实时推送意见反馈消息
//     * @param $chanel
//     */
//    public function pushFeedBackMessage($chanel){
//        try {
//            $sse = new SSE();
//            $sse->sleep_time = 1;   #数据发送后睡眠时间
//            $sse->exec_limit = 10;  #脚本时间限制
//            $sse->allow_cors = true;
//
//            $sse->start();
//        } catch (\Exception $e) {
//            Loggy::write('error',$e->getMessage(),$e);
//        }
//    }
}