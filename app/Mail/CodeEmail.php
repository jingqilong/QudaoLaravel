<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 邮件内容
     * @var string
     */
    public $content = '';
    /**
     * 邮件验证码标题
     * @var string
     */
    public $title = '';

    /**
     * Create a new message instance.
     *
     * @param $content
     * @param $title
     */
    public function __construct($content,$title)
    {
        $this->content  = $content;
        $this->title    = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'mails.code.html.code';
        return $this->from(config("mail.from.address"),config("mail.from.name"))
            ->markdown($view)
            ->subject('【渠道PLUS】'.$this->title)       //邮件标题
            ->with([
                'content'     => $this->content,                        //邮件内容
            ]);
    }
}
