<?php

namespace App\Console\Commands;

use App\Enums\ShopOrderEnum;
use App\Repositories\ShopOrderRelateRepository;
use App\Repositories\ShopOrderRelateViewRepository;
use Illuminate\Console\Command;
use Tolawho\Loggy\Facades\Loggy;

class OrderAutoReceived extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:auto-received';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单自动收货';

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
        $receive = config('common.shop.order_receive_ttl',14);
        $shipment_at = strtotime(date('Y-m-d H:i')) - (86400 * $receive);
        $where = ['status' => ShopOrderEnum::SHIPPED,'shipment_at' => ['<',$shipment_at]];
        if (!$order_list = ShopOrderRelateViewRepository::getAllList($where,['id','order_no'])){
            print '无符合条件订单'.PHP_EOL;
            return true;
        }
        print '有符合条件订单'.PHP_EOL;
        foreach ($order_list as $item){
            #自动收货
            $upd = ['status' => ShopOrderEnum::RECEIVED,'receive_at' => time(),'updated_at' => time()];
            if (!ShopOrderRelateRepository::getUpdId(['id' => $item['id']],$upd)){
                Loggy::write('autotask','商城订单超时自动收货 | 订单自动收货失败！订单号：'.$item['order_no']);
                print '自动收货失败'.PHP_EOL;
            }else{
                print '自动收货成功'.PHP_EOL;
            }
        }
        print '结束'.PHP_EOL;
        return true;
    }
}
