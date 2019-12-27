<?php


namespace App\Listeners;
/**
 * Class ProcessEventSubscriber
 * @package App\Listeners
 * @deprecated true
 * @desc 目前已将事件放到队列中了，这里暂时保留
 */
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