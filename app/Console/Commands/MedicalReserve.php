<?php

namespace App\Console\Commands;

use App\Enums\DoctorEnum;
use App\Enums\MessageEnum;
use App\Repositories\MedicalOrdersRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use Illuminate\Console\Command;

class MedicalReserve extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reserve:medical';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '医疗预约提醒';

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
        $where = ['status' => DoctorEnum::PASS,'deleted_at' => 0,'appointment_at' => ['range',[$tomorrow_start,$tomorrow_end]]];
        if (!$reserve_list = MedicalOrdersRepository::getAllList($where)){
            print '没有符合条件预约  ';
            return true;
        }
        print '有符合条件预约  ';
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($reserve_list as $reserve){
            $appointment_at = strtotime($reserve['appointment_at']);
            $simple_time= date('H点i分',$appointment_at);
            $content    = '尊敬的用户您好！明天'.$simple_time.'您有一个医疗预约，请合理安排时间，我们期待您的赴约！';
            $title      = '医疗预约提醒';
            //短信通知
            if (isset($reserve['mobile']))
                $SmsService->sendContent($reserve['mobile'],$content);
            //站内信通知
            $MessageService->sendMessage($reserve['member_id'],MessageEnum::LOANBOOKING,$title,$content,$reserve['id']);
            print '通知完成  ';
        }
        print '结束  ';
        return true;
    }
}
