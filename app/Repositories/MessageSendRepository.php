<?php


namespace App\Repositories;


use App\Enums\MessageEnum;
use App\Models\MessageSendModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Cache;
use function Qiniu\base64_urlSafeEncode;

class MessageSendRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageSendModel $model)
    {
        $this->model = $model;
    }
//
//    /**
//     * 增加缓存中消息数量
//     * @param $user_id
//     * @param $user_type
//     */
//    protected function increaseCacheMessageCount($user_id, $user_type){
//        $index      = config('message.cache-chanel');
//        // $user_index = base64_encode($index[$user_type].$user_id);
//        $user_index = base64_urlSafeEncode($index[$user_type].$user_id);
//
//        $key        = config('message.cache-key');
//        $all_message_count = Cache::has($key) ? Cache::get($key) : [];
//        if (empty($user_id)){
//            foreach ($all_message_count as &$count){++$count;}
//        }else{
//            $all_message_count[$user_index] = isset($all_message_count[$user_index]) ? ++$all_message_count[$user_index] : 1;
//        }
//        Cache::put($key,$all_message_count);
//    }
//    /**
//     * 减少缓存中消息数量
//     * @param $user_id
//     * @param $user_type
//     */
//    protected function decrementCacheMessageCount($user_id, $user_type){
//        $index      = config('message.cache-chanel');
//        // $user_index = base64_encode($index[$user_type].$user_id);
//        $user_index = base64_urlSafeEncode($index[$user_type].$user_id);
//
//        $key        = config('message.cache-key');
//        $all_message_count = Cache::has($key) ? Cache::pull($key) : [];
//        $count = $all_message_count[$user_index] ?? 0;
//        $all_message_count[$user_index] = empty($count) ? 0 : $all_message_count[$user_index] - 1;
//        Cache::put($key,$all_message_count);
//    }
}
            