<?php

namespace App\Listener;

use App\Events\SendDingTalkEmail;
use App\Events\SendWeChatPush;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;

class ProcessEventListener
{

    public function onSendDingTalkEmail(SendDingTalkEmail $event) {
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