<?php

namespace App\Listeners;

use App\Events\SendDingTalkEmail;
use App\Events\SendWeChatPush;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;

/**
 * Class ProcessEventListener
 * @package App\Listener
 */
class ProcessEventListener
{
    /**
     * ProcessEventListener constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param SendDingTalkEmail $event
     * @return bool
     */
    public function onSendDingTalkEmail(SendDingTalkEmail $event) {
        //dd($event);
         dd($event->data);
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }


    public function onSendFlowSms(SendFlowSms $event) {
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }

    public function onSendSiteMessage(SendSiteMessage $event) {
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }


    public function onSendWeChatPush(SendWeChatPush $event) {
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }
}