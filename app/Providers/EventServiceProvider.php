<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\SendDingTalkEmail;
use App\Events\SendWeChatPush;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;
use App\Listener\ProcessEventSubscriber;



class EventServiceProvider extends ServiceProvider
{
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
            ProcessEventSubscriber::class,
        ],
        SendWeChatPush::class => [
            //微信推送 流程订阅都监听
            ProcessEventSubscriber::class,
        ],
        SendSiteMessage::class => [
            //站内信息 流程订阅都监听
            ProcessEventSubscriber::class,
        ],
        SendFlowSms::class => [
            //短信 流程订阅都监听
            ProcessEventSubscriber::class,
        ],
    ];

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
