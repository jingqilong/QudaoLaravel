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
            'App\Events\SendDingTalkEmail',
            'App\Listeners\ProcessEventListener@onSendDingTalkEmail'
        );

        $events->listen(
            'App\Events\SendFlowSms',
            'App\Listeners\ProcessEventListener@onSendFlowSms'
        );

        $events->listen(
            'App\Events\SendSiteMessage',
            'App\Listeners\ProcessEventListener@onSendSiteMessage'
        );

        $events->listen(
            'App\Events\SendWeChatPush',
            'App\Listeners\ProcessEventListener@onSendWeChatPush'
        );
    }
}