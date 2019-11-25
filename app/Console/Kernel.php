<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Tolawho\Loggy\Facades\Loggy;

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
