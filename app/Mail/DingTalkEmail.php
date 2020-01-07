<?php

namespace App\Mail;

use App\Enums\ProcessActionEventTypeEnum;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
//以下是队列需要的
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


class DingTalkEmail extends Mailable
{
    //use Queueable, SerializesModels;
    //事件侦听中添加了队列，这里先删除。免得重复
    use SerializesModels;
    protected $mail_data;

    /**
     * Create a new message instance.
     * @param $mail_data
     * @return void
     */
    public function __construct($mail_data)
    {
        $this->mail_data = $mail_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /**
         * 根据不同的事件类型，使用不同的邮件模板
         */
        if(ProcessActionEventTypeEnum::NODE_EVENT == $this->mail_data['event_type']){
            $view = 'mails.events.html.task';
        }
        if(ProcessActionEventTypeEnum::ACTION_RESULT_EVENT == $this->mail_data['event_type']){
            $view = 'mails.events.html.notice';
        }
        return $this->from(config("mail.from.address"),config("mail.from.name"))
            ->to($this->mail_data['receiver_email'],$this->mail_data['receiver_name'])
            ->markdown($view)
            ->subject($this->mail_data['title'])                                //邮件标题
            ->with([
                'receiver_name'     => $this->mail_data['receiver_name'],       //收件姓名
                'process_full_name' => $this->mail_data['process_full_name'],   //流程全称
                'link_url'          => $this->mail_data['link_url'],            //OA的链接
                'precess_result'    => $this->mail_data['precess_result']       //审核结果的文字说明
            ]);
    }
}
