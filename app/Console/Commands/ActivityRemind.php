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

class ActivityRemind extends Command
{
    use HelpTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '活动开始前，提醒已参加的用户。';

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
        $tomorrow_start = strtotime('+1 day '.date('Y-m-d')."00:00:00");
        $tomorrow_end   = strtotime('+1 day '.date('Y-m-d')."24:00:00");
        $where          = ['start_time' => ['range',[$tomorrow_start,$tomorrow_end]],'status' => ActivityEnum::OPEN,'deleted_at' => 0];
        //获取第二天即将开始的活动列表
        if (!$activity_list = ActivityDetailRepository::getList($where,['id','name','area_code','address','start_time'])){
            return true;
        }
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($activity_list as $activity){
            //获取已经参加活动的用户
            if (!$register_list = ActivityRegisterRepository::getList(['activity_id' => $activity['id'],'status' => ['in',[ActivityRegisterEnum::EVALUATION]]])){
                continue;
            }
            list($area) = $this->makeAddress($activity['area_code'],$activity['address'],3);
            $content    = '尊敬的用户您好！您参加的活动《'.$activity['name'].'》与明天'.date('H点i分',$activity['start_time']).
                '在'.$area.'举行，请您合理安排时间准时参加，谢谢！';
            $title      = '活动即将开始';
            foreach ($register_list as $register){
                //短信通知
                if (isset($register['mobile']))
                $SmsService->sendContent($register['mobile'],$content);
                //站内信通知
                $MessageService->sendMessage($register['member_id'],MessageEnum::ACTIVITYENROLL,$title,$content);
            }
        }
        return true;
    }
}
