<?php


namespace App\Listeners;

class ProcessEventSubscriber
{

    /**
     * 为订阅者注册监听器
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {

        $events->listen(
            'App\Listeners\ProcessEventListener@onSendDingTalkEmail'
        );

        $events->listen(
            'App\Listeners\ProcessEventListener@onSendFlowSms'
        );

        $events->listen(
            'App\Listeners\ProcessEventListener@onSendSiteMessage'
        );

        $events->listen(
           'App\Listeners\ProcessEventListener@onSendWeChatPush'
        );
    }
}