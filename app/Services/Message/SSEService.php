<?php

namespace App\Services\Message;

use App\Services\BaseService;
use App\SSEEvent\MessageCountEvent;
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
            $sse->start();
        } catch (\Exception $e) {
            Loggy::write('error',$e->getMessage(),$e);
        }
    }
}