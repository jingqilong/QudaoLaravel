<?php


namespace App\Services\Message;


use App\Services\BaseService;
use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\Update;
use Illuminate\Support\Facades\Cache;

class SSEService extends BaseService
{
    public function newMsgs()
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');//Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
        $response->setCallback(function () {
            (new SSE())->start(new Update(function () {
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
            }), 'new-msgs');
            (new SSE())->start(new Update(function () {
                $number = rand(1, 1000);
                return json_encode(['number' => $number]);
            }), 'new-message-number');
        });
        return $response;
    }
}