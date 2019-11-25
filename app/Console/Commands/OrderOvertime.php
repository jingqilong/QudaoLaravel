<?php

namespace App\Console\Commands;

use App\Enums\ShopOrderEnum;
use App\Repositories\ShopOrderRelateRepository;
use Illuminate\Console\Command;

class OrderOvertime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:overtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '商城订单超时取消订单';

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
        $where = ['status' => ShopOrderEnum::PAYMENT,'created_at' => ['<',(time() - 86400)]];
        if ($order_list = ShopOrderRelateRepository::getList($where,['id','order_id','member_id'])){

        }
    }
}
