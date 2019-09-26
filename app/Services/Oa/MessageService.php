<?php
namespace App\Services\Oa;

use App\Models\OaEmployeeModel;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaPushSubscriptionsRepository;
use App\Traits\HelpTrait;
use ErrorException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Tolawho\Loggy\Facades\Loggy;

class MessageService extends Notification
{
    use HelpTrait;
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

    /**
     * 发送推送
     * @param $user_id
     * @param $title
     * @param $content
     * @param $icon
     * @param $action_title
     * @param $action
     * @return bool
     */
    public function push($user_id, $title, $content, $icon, $action_title, $action){
        if (!$push_subscriptions = OaPushSubscriptionsRepository::getList(['subscribable_id' => $user_id])){
            $this->error = '用户未授权！';
            return false;
        }
        $notifications = [];
        try{
            foreach ($push_subscriptions as $value){
                $notifications[] = [
                    'subscription' => Subscription::create([
                        'endpoint' => $value['endpoint'],
                        'publicKey' => $value['public_key'], // base 64 encoded, should be 88 chars
                        'authToken' => $value['auth_token'], // base 64 encoded, should be 24 chars
                    ]),
                    'payload' => 'hello!',
                ];
            }
            $webPush = new WebPush();
            //发送带有负载的多个通知
            foreach ($notifications as $notification) {
                $webPush->sendNotification(
                    $notification['subscription'],
                    $notification['payload'] // optional (defaults null)
                );
            }

            /**
             * 检查发送的结果
             * @var MessageSentReport $report
             */
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if ($report->isSuccess()) {
                    echo "[v] Message sent successfully for subscription {$endpoint}.";
                    $this->message = '发送成功！';
                    return true;
                } else {
                    echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
                    $this->error = '发送失败！';
                    return false;
                }
            }
        } catch (ErrorException $e) {
            $this->error = $e->getMessage();
            return false;
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
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
            