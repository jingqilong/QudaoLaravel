<?php

namespace App\Console\Commands;

use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Library\Time\Lunar;
use App\Repositories\MemberViewRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use Illuminate\Console\Command;

class BlessingFestival extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blessing:festival';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '会员节日祝福';

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
        $time = strtotime('2020-01-24');
        $lunar_time = app(Lunar::class)->festival($time);
        if (empty($lunar_time)){
            return true;
        }
        $where = [
            'deleted_at' => 0,
            'status' => 0,
            'grade' => [
                'in',
                [
                    MemberEnum::ALSOENJOY,
                    MemberEnum::TOENJOY,
                    MemberEnum::YUEENJOY,
                    MemberEnum::REALLYENJOY,
                    MemberEnum::YOUENJOY,
                    MemberEnum::HONOURENJOY,
                    MemberEnum::ZHIRENJOY,
                    MemberEnum::ADVISER
                ]
            ]
        ];
        $where = ['id' => 314];
        if (!$member_list = MemberViewRepository::getList($where)){
            print '没有符合条件预约  ';
            return true;
        }
        print '有符合条件预约  ';
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($member_list as $value){
            $member_name = !empty($value['ch_name']) ? $value['ch_name'] : (!empty($value['en_name']) ? $value['en_name'] : '会员');
            $member_name = $member_name.MemberEnum::getSex($value['sex']);
            $content    = '尊敬的'.$member_name.'，在您的生日到来之时，愿我们的祝福为您的生日添彩，祝您生日快乐，年年今日，岁岁今朝！';
            $title      = '生日祝福';
            //短信通知
            if (isset($value['mobile']))
                $SmsService->sendContent($value['mobile'],$content);
            //站内信通知
            $MessageService->sendMessage($value['id'],MessageEnum::SYSTEMNOTICE,$title,$content);
            print '通知完成  ';
        }
        print '结束  ';
        return true;
    }
}
