<?php


namespace App\Listener;

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
            'Illuminate\Auth\Events\SendDingTalkEmail',
            'App\Listeners\ProcessEventListener@onSendDingTalkEmail'
        );

        $events->listen(
            'Illuminate\Auth\Events\SendFlowSms',
            'App\Listeners\ProcessEventListener@onSendFlowSms'
        );

        $events->listen(
            'Illuminate\Auth\Events\SendSiteMessage',
            'App\Listeners\ProcessEventListener@onSendSiteMessage'
        );

        $events->listen(
            'Illuminate\Auth\Events\SendWeChatPush',
            'App\Listeners\ProcessEventListener@onSendWeChatPush'
        );
    }
}