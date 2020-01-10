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
     * 邮件验证码
     * @var string
     */
    public $code = '';

    /**
     * Create a new message instance.
     *
     * @param $content
     * @param $code
     */
    public function __construct($content,$code)
    {
        $this->content  = $content;
        $this->code     = $code;
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
            ->subject('【渠道PLUS】邮箱验证码:'.$this->code)       //邮件标题
            ->with([
                'content'     => $this->content,                        //邮件内容
            ]);
    }
}
