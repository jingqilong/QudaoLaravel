<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;




class EventServiceProvider extends ServiceProvider
{
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        '\App\Listeners\ProcessEventSubscriber',
    ];
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SendDingTalkEmail::class => [
            //钉钉邮件通知 流程订阅都监听
            \App\Listeners\SendDingTalkEmailListener::class,
        ],
        SendWeChatPush::class => [
            //微信推送 流程订阅都监听
            \App\Listeners\SendWeChatPushListener::class,
            //ProcessEventSubscriber::class,
        ],
        SendSiteMessage::class => [
            //站内信息 流程订阅都监听
            \App\Listeners\SendSiteMessageListener::class,
        ],
        SendFlowSms::class => [
            //短信 流程订阅都监听
            \App\Listeners\SendFlowSmsListener::class,
        ],    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
