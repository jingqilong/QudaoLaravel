<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ActivityRemind::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 活动签到提醒
        $schedule->command('activity:remind')
        ->dailyAt('13:00');//每天13点运行

        // 活动即将开始提醒
        $schedule->command('activity:register')
        ->everyMinute();//每分钟运行

        // 订单超时关闭提醒
        $schedule->command('order:overtime')
        ->everyMinute();//每分钟运行

        //企业咨询预约即将到时提醒
        $schedule->command('reserve:consult')
            ->dailyAt('20:00');//每天20点运行

        //看房预约即将到时提醒
        $schedule->command('reserve:house')
            ->dailyAt('20:00');//每天20点运行

        //贷款预约即将到时提醒
        $schedule->command('reserve:loan')
            ->dailyAt('20:00');//每天20点运行

        //医疗预约即将到时提醒
        $schedule->command('reserve:medical')
            ->dailyAt('20:00');//每天20点运行

        //精选生活预约即将到时提醒
        $schedule->command('reserve:prime')
            ->dailyAt('20:00');//每天20点运行

        //项目对接预约即将到时提醒
        $schedule->command('reserve:project')
            ->dailyAt('20:00');//每天20点运行

//        //生日祝福
//        $schedule->command('blessing:birthday')
//            ->dailyAt('10:00')
//            ->weekdays();//工作日10点
//        //生日祝福
//        $schedule->command('blessing:birthday')
//            ->dailyAt('11:00')
//            ->saturdays();//周末11点

        //访问量存储
        $schedule->command('record:pv')
            ->everyMinute();//每分钟运行
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
