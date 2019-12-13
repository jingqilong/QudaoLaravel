<?php

namespace App\Console\Commands;

use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterEnum;
use App\Enums\MessageEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Console\Command;

class ActivityRegister extends Command
{
    use HelpTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '活动开始后，通知用户签到。';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $where          = ['end_time' => ['>',time()],'status' => ActivityEnum::OPEN,'deleted_at' => 0];
        //获取第二天即将开始的活动列表
        if (!$activity_list = ActivityDetailRepository::getList($where,['id','name','start_time','signin'])){
            return true;
        }
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($activity_list as $activity){
            if (($activity['signin'] * 60 + $activity['start_time']) !== strtotime(date('Y-m-d H:i').":00")){
                continue;
            }
            //获取已经参加活动的用户
            $activity_where = ['activity_id' => $activity['id'],'status' => ['in',[ActivityRegisterEnum::EVALUATION,ActivityRegisterEnum::COMPLETED]]];
            if (!$register_list = ActivityRegisterRepository::getList($activity_where)){
                continue;
            }
            $sms_content    = '尊敬的用户您好！您参加的活动《'.$activity['name'].'》可以开始签到了，打开微信进入公众号->我的->活动报名->立即签到，前往公众号即可签到！';
            $mes_content    = '尊敬的用户您好！您参加的活动《'.$activity['name'].'》可以开始签到了，点击按钮立即签到！';
            $title          = '活动签到提醒';
            foreach ($register_list as $register){
                //短信通知
                if (isset($register['mobile']))
                    $SmsService->sendContent($register['mobile'],$sms_content);
                //站内信通知
                $MessageService->sendMessage($register['member_id'],MessageEnum::ACTIVITYCHECK,$title,$mes_content,$activity['id'],null,$register['sign_in_code']);
            }
        }
        return true;
    }
}
