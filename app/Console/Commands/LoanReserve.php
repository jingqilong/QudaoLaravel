<?php

namespace App\Console\Commands;

use App\Enums\LoanEnum;
use App\Enums\MessageEnum;
use App\Repositories\LoanPersonalRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use Illuminate\Console\Command;

class LoanReserve extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reserve:loan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '贷款预约提醒';

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
        $where = ['status' => LoanEnum::PASS,'deleted_at' => 0,'reservation_at' => ['range',[$tomorrow_start,$tomorrow_end]]];
        if (!$reserve_list = LoanPersonalRepository::getList($where)){
            print '没有符合条件预约  ';
            return true;
        }
        print '有符合条件预约  ';
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($reserve_list as $reserve){
            $simple_time= date('H点i分',$reserve['reservation_at']);
            $content    = '尊敬的用户您好！明天'.$simple_time.'您有一个预约，预约地点：'.$reserve['address'].'，请合理安排时间，我们期待您的赴约！';
            $title      = '贷款预约提醒';
            //短信通知
            if (isset($reserve['mobile']))
                $SmsService->sendContent($reserve['mobile'],$content);
            //站内信通知
            $MessageService->sendMessage($reserve['user_id'],MessageEnum::LOANBOOKING,$title,$content,$reserve['id']);
            print '通知完成  ';
        }
        print '结束  ';
        return true;
    }
}
