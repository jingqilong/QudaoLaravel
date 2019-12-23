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
            //�����ʼ�֪ͨ ���̶��Ķ�����
            \App\Listeners\SendDingTalkEmailListener::class,
        ],
        SendWeChatPush::class => [
            //΢������ ���̶��Ķ�����
            \App\Listeners\SendWeChatPushListener::class,
            //ProcessEventSubscriber::class,
        ],
        SendSiteMessage::class => [
            //վ����Ϣ ���̶��Ķ�����
            \App\Listeners\SendSiteMessageListener::class,
        ],
        SendFlowSms::class => [
            //���� ���̶��Ķ�����
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
