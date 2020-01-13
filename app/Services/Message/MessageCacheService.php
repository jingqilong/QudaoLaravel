<?php


namespace App\Services\Message;


use App\Enums\MessageEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

class MessageCacheService extends BaseService
{
    /**
     * 增加缓存中消息数量
     * @param $user_id
     * @param $user_type
     */
    protected function increaseCacheMessageCount($user_id, $user_type){
        $index      = config('message.cache-chanel');
        $key        = config('message.cache-key');
        $all_message_count = Cache::has($key) ? Cache::get($key) : [];
        if (!empty($user_id)){
            $user_index = base64UrlEncode($index[$user_type].$user_id);
            $all_message_count[$user_index] = isset($all_message_count[$user_index]) ? ++$all_message_count[$user_index] : 1;
            Cache::put($key,$all_message_count);
            return;
        }//如果消息类型为公告，需为每个用户写缓存
        $user_list = [];
        if (MessageEnum::MEMBER == $user_type){
            $user_list = MemberBaseRepository::getAll(['id']);
        }
        if (MessageEnum::MERCHANT == $user_type){
            $user_list = PrimeMerchantRepository::getAll(['id']);
        }
        if (MessageEnum::OAEMPLOYEES == $user_type){
            $user_list = OaEmployeeRepository::getAll(['id']);
        }
        $user_ids = !empty($user_list) ? array_column($user_list,'id') : [];
        foreach ($user_ids as $id){
            $user_index = base64UrlEncode($index[$user_type].$id);
            $all_message_count[$user_index] = isset($all_message_count[$user_index]) ? ++$all_message_count[$user_index] : 1;
        }
        Cache::put($key,$all_message_count);
    }
    /**
     * 减少缓存中消息数量
     * @param $user_id
     * @param $user_type
     */
    protected function decrementCacheMessageCount($user_id, $user_type){
        $index      = config('message.cache-chanel');
        // $user_index = base64_encode($index[$user_type].$user_id);
        $user_index = base64UrlEncode($index[$user_type].$user_id);

        $key        = config('message.cache-key');
        $all_message_count = Cache::has($key) ? Cache::pull($key) : [];
        $count = $all_message_count[$user_index] ?? 0;
        $all_message_count[$user_index] = empty($count) ? 0 : $all_message_count[$user_index] - 1;
        Cache::put($key,$all_message_count);
    }
}