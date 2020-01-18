<?php

namespace App\Console\Commands;

use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Library\Time\Lunar;
use App\Repositories\MemberViewRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use Illuminate\Console\Command;

class BlessingBirthday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blessing:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '会员生日祝福';

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
        $lunar_time = date('m-d',app(Lunar::class)->S2L(date('Y-m-d'))).' 00:00:00';
        $where = ['birthday' => ['like','%'.$lunar_time]];
        $where = ['id' => 314];
        if (!$member_list = MemberViewRepository::getAllList($where)){
            print '没有符合条件会员  ';
            return true;
        }
        print '有符合条件会员  ';
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
