<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tolawho\Loggy\Facades\Loggy;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试';

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
        Loggy::write('debug','测试自动任务正在进行：time：'.date('Y-m-d H:i:s'));
    }
}
