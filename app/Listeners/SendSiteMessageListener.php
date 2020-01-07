<?php

namespace App\Listeners;

use App\Enums\MessageEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Services\Message\MessageTemplate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Message\SendService;
use Tolawho\Loggy\Facades\Loggy;


class SendSiteMessageListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return bool
     */
    public function handle($event)
    {
        $data       = $event->data;
        $receiver   = $data['receiver'];
        Loggy::write('process',$receiver['receiver_mobile'].'执行了发送站内信事件！');
        $message_data = [
            'receiver_name'     => $receiver['receiver_name'],
            'process_full_name' => $data['process_full_name']['process_name'],
        ];
        $category = MessageEnum::SYSTEMNOTICE;
        $messageTemplate = new MessageTemplate($message_data,$receiver['receiver_iden']);
        if ($data['event_type'] == ProcessPrincipalsEnum::STARTER){
            app(SendService::class)::sendMessageForMember(
                $receiver['receiver_id'],
                $category,
                $data['title'],
                $messageTemplate->getContent(),
                $data['business_id']
            );
            return false;
        }
        app(SendService::class)::sendMessageForEmployee(
            $receiver['receiver_id'],
            $category,
            $data['title'],
            $messageTemplate->getContent(),
            $data['business_id'],
            $data['link_url']
        );
        return false;
    }
}
