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
        \App\Console\Commands\Test::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('test')//Test.php中的name
        ->everyMinute()
            ->before(function () {
                Loggy::write('debug','测试自动任务即将开始：time：'.date('Y-m-d H:i:s'));
                // 任务就要开始了…
            })
            ->after(function () {
                Loggy::write('debug','测试自动任务结束：time：'.date('Y-m-d H:i:s'));
                // 任务完成…
            })
        ;//每五分钟执行一次
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
