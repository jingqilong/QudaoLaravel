<?php

namespace App\Listeners;

use App\Events\SendDingTalkEmail;
use App\Events\SendWeChatPush;
use App\Events\SendSiteMessage;
use App\Events\SendFlowSms;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;

/**
 * Class ProcessEventListener
 * @package App\Listener
 * @deprecated true
 * @desc 目前已将事件放到队列中了，这里暂时保留
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
     * 发送钉邮
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


    /**
     * 发送短信
     * @param SendFlowSms $event
     * @return bool
     */
    public function onSendFlowSms(SendFlowSms $event) {
        $data = $event->data;
        app(SmsService::class)->sendContent($data['mobile'],$data['content']);
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }

    /**
     * 发送站内信
     * @param SendSiteMessage $event
     * @return bool
     */
    public function onSendSiteMessage(SendSiteMessage $event) {
        $data = $event->data;
        app(SendService::class)::sendMessageForEmployee(
            $data['employee_id'],
            $data['category'],
            $data['title'],
            $data['content'],
            $data['relate_id'],
            $data['url'],
            $data['image_ids']
        );
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }


    /**
     * 发送微信推送
     * @param SendWeChatPush $event
     * @return bool
     */
    public function onSendWeChatPush(SendWeChatPush $event) {
        //接收到事件时，拿$event中的数据去调用对应的Service
        //完成后返回false,则其它监听器再也听不到了
        return false;
    }
}