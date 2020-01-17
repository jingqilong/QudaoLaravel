<?php

namespace App\Console\Commands;

use App\Enums\MessageEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\ShopOrderRelateViewRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Services\Shop\OrderRelateService;
use Illuminate\Console\Command;
use Tolawho\Loggy\Facades\Loggy;

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
        $yesterday_start = strtotime(date('Y-m-d H:i')) - 86400;
        $yesterday_end   = strtotime(date('Y-m-d H:i')) - 86340;
        $where = ['status' => ShopOrderEnum::PAYMENT,'created_at' => ['range',[$yesterday_start,$yesterday_end]]];
        if (!$order_list = ShopOrderRelateViewRepository::getAllList($where,['*'])){
            return true;
        }
        print '有符合条件订单  ';
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($order_list as $item){
            #取消订单
            $orderRelateService = new OrderRelateService();
            #如果取消失败，执行3次
            if (!$orderRelateService->offOrder($item)){
                Loggy::write('autotask','商城订单超时取消订单 | 订单自动取消失败！订单号：'.$item['order_no']);
            }
            print '订单取消成功  ';
            $content    = '尊敬的用户您好！您'.$item['created_at'].'的订单：'.$item['order_no'].'，由于长时间未支付，系统已自动取消，如需再次购买，需重新下单，感谢您的使用！';
            $title      = '订单超时关闭提醒';
            //短信通知
            $SmsService->sendContent($item['member_mobile'],$content);
            //站内信通知
            $MessageService->sendMessage($item['member_id'],MessageEnum::SHOPOORDER,$title,$content,$item['id']);
        }
        print '结束';
        return true;
    }
}
