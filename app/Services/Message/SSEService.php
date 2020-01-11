<?php


namespace App\Services\Message;


use App\Services\BaseService;
use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\Update;

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
                $id = mt_rand(1, 1000);
                $newMsgs = [['id' => $id, 'title' => 'title' . $id, 'content' => 'content' . $id]];//get data from database or service.
                if (!empty($newMsgs)) {
                    return json_encode(['newMsgs' => $newMsgs]);
                }
                return false;//return false if no new messages
            }), 'new-msgs');
        });
        return $response;
    }
}