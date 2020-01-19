<?php

namespace App\Console\Commands;

use App\Enums\MessageEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MessageSendRepository;
use App\Repositories\MessageReadRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\PrimeMerchantRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncMsgCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:msg-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步缓存中的消息数量';

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
        print '命令开始！'.PHP_EOL;
        $start_time = time();
        $index      = config('message.cache-chanel');
        $key        = config('message.cache-key');
        #获取所有用户【员工、成员、商户】
        $all_user_list = $this->getAllUserList();
        #获取所有已发送消息
        $all_send_list = MessageSendRepository::getAllList(['deleted_at' => null],['id','user_id','user_type']);
        #获取所有已读消息
        $all_read_list = MessageReadRepository::getAll(['id','send_id','user_id','user_type']);
        $all_read_list = $this->createReadListIndex($all_read_list);
        #将消息条数读入数组
        $all_message_count = [];
        foreach ($all_send_list as $item){
            if ($item['user_id'] != 0){#非公告类型
                if (isset($all_read_list[$item['id'] . '.' . $item['user_id'] . '.' . $item['user_type']])){
                    continue;
                }
                $user_index = base64UrlEncode($index[$item['user_type']].$item['user_id']);
                $all_message_count[$user_index] = isset($all_message_count[$user_index]) ? ++$all_message_count[$user_index] : 1;
                continue;
            }
            #公告类型，需要给每个未读消息的人加入数量
            foreach ($all_user_list as $user){
                if ($user['user_type'] != $item['user_type']) continue;
                if (isset($all_read_list[$item['id'] . '.' . $user['user_id'] . '.' . $item['user_type']])){
                    continue;
                }
                $user_key = base64UrlEncode($index[$item['user_type']].$user['user_id']);
                $all_message_count[$user_key] = isset($all_message_count[$user_key]) ? ++$all_message_count[$user_key] : 1;
            }
        }
        #将未读消息数据写入缓存
        Cache::forget($key);
        Cache::put($key,$all_message_count,null);
        print '执行完毕！耗时：'.(time() - $start_time).'s'.PHP_EOL;
        return true;
    }

    /**
     * 获取所有用户列表
     * @return array
     */
    private function getAllUserList(){
        $all_user_list     = [];
        #员工列表
        $all_employee_list = OaEmployeeRepository::getAll(['id']);
        $all_user_list     += $this->setUserType($all_employee_list,MessageEnum::OAEMPLOYEES);
        #商户列表
        $all_merchant_list = PrimeMerchantRepository::getAll(['id']);
        $all_user_list     += $this->setUserType($all_merchant_list,MessageEnum::MERCHANT);
        #成员列表
        $all_member_list   = MemberBaseRepository::getAll(['id']);
        $all_user_list     += $this->setUserType($all_member_list,MessageEnum::MEMBER);
        return $all_user_list;
    }

    /**
     * 设置用户类别
     * @param $list
     * @param $user_type
     * @return array
     */
    private function setUserType($list, $user_type){
        $res_list = [];
        foreach ($list as $v){
            $res_list[] = [
                'user_id'   => $v['id'],
                'user_type' => $user_type
            ];
        }
        return $res_list;
    }

    /**
     * 给已读列表加索引
     * @param $read_list
     * @return array
     */
    private function createReadListIndex($read_list){
        $res = [];
        foreach ($read_list as $v){
            $res[$v['send_id'] . '.' . $v['user_id'] . '.' . $v['user_type']] = $v;
        }
        return $res;
    }
}
