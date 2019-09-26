<?php
namespace App\Services\Oa;

use App\Models\OaEmployeeModel;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaPushSubscriptionsRepository;
use App\Traits\HelpTrait;
use ErrorException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Tolawho\Loggy\Facades\Loggy;

class MessageService extends Notification
{
    /**
     * 错误信息
     * @var
     *
     */
    public $error;

    /**
     * 提示信息
     * @var
     */
    public $message;

    use HelpTrait;

    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }

    /**
     * 添加推送授权信息
     * @param array $push_subscription
     * @return mixed
     */
    public function addPushAuth(array $push_subscription)
    {
        $user = $this->auth->user();
        $model = OaEmployeeModel::find($user->id);
        if (!$model->updatePushSubscription(
            $push_subscription['endpoint'],
            $push_subscription['public_key'],
            $push_subscription['auth_token'],
            $push_subscription['content_encoding'])){
            $this->error = '授权信息添加失败！';
            return false;
        }
        $this->message = '授权信息添加成功！';
        return true;
    }

    public function push($user_id, $title, $content, $icon, $action_title, $action){
        $message =  (new WebPushMessage)
            ->title($title)
            ->icon($icon)
            ->body($content)
            ->action($action_title,$action);
        return $message;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Approved!')
            ->icon('/approved-icon.png')
            ->body('Your account was approved!')
            ->action('View account', 'view_account');
        // ->data(['id' => $notification->id])
        // ->badge()
        // ->dir()
        // ->image()
        // ->lang()
        // ->renotify()
        // ->requireInteraction()
        // ->tag()
        // ->vibrate()
    }
}
            