<?php
/**
 * author:sang
 * 事件处理器
 * 作用：用于处理流程中的事件相关操作
 */
namespace App\Services\Common;

use App\Exceptions\ServiceException\EventDoesNotExistsException;
use App\Services\BaseService;

class EventProcessorService extends BaseService
{
    #存在的事件列表
    private static $event_list = [
        'send_sms'      => 'sendSms',
        'send_email'    => 'sendEmail',
        'send_push'     => 'sendPush'
    ];


    /**
     * EventReceiver    事件接收器
     * 作用：用于接收需要触发的事件
     * @param $event
     * @param mixed ...$parameter
     * @return mixed
     * @throws EventDoesNotExistsException
     */
    public static function eventReceiver($event, ...$parameter){
        if (!isset(self::$event_list[$event])){
            throw new EventDoesNotExistsException('事件不存在！');
        }
        return self::{self::$event_list[$event]}(...$parameter);
    }


    /**
     * 发送短信
     * @param string $mobile    手机号
     * @param string $content   短信内容
     * @return array
     */
    private static function sendSms($mobile, $content){
        $smsService = new SmsService();
        return $smsService->sendContent($mobile,$content);
    }

    /**
     * 发送邮件
     * @param string $email     邮箱地址
     * @param string $content   邮件内容
     */
    private static function sendEmail($email, $content){}

    /**
     * 发送邮件
     * @param string $email     邮箱地址
     * @param string $content   邮件内容
     */
    private static function sendPush($email, $content){}
}