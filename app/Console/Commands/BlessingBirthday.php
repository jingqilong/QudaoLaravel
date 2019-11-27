<?php

namespace App\Console\Commands;

use App\Enums\MemberEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberRepository;
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
//        $list = MemberRepository::getAll();
//        foreach ($list as $value){
//            if (!empty($value['m_time']))
//            MemberBaseRepository::getUpdId(['id' => $value['m_id']],['created_at' => strtotime($value['m_time'])]);
//        }
        print 'ok';
    }
}
