<?php

namespace App\Console\Commands;

use App\Enums\MessageEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\ShopOrderRelateViewRepository;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Services\Shop\OrderRelateService;
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
        $where = ['status' => ShopOrderEnum::PAYMENT];
        if (!$order_list = ShopOrderRelateViewRepository::getList($where,['*'])){
            return true;
        }
        print '有订单  ';
        $SmsService     = new SmsService();
        $MessageService = new SendService();
        foreach ($order_list as $item){
            if ((strtotime($item['created_at'])) > (strtotime(date('Y-m-d H:i').":00") - 86400) &&
                (strtotime($item['created_at'])) < (strtotime(date('Y-m-d H:i').":59") - 86400)
            ){
                print '不符合条件订单  ';
                continue;
            }
            print '有符合条件订单  ';
            #取消订单
            $orderRelateService = new OrderRelateService();
            #如果取消失败，执行3此
            if (!$orderRelateService->offOrder($item)){
                if (!$orderRelateService->offOrder($item)){
                    if(!$orderRelateService->offOrder($item)){
                        continue;
                    }
                }
            }
            print '订单取消成功  ';
            $content    = '尊敬的用户您好！您订单：'.$item['order_no'].'，由于长时间未支付，系统已自动取消，如需再次购买，需重新下单，感谢您的使用！';
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
