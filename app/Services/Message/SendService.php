<?php
namespace App\Services\Message;


use App\Enums\MessageEnum;
use App\Repositories\MessageSendRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class SendService extends BaseService
{
    /**
     * 发送系统消息
     * @param $user_id
     * @param $user_type
     * @param $title
     * @param $content
     * @param null $relate_id
     * @param null $image_ids
     * @param null $url
     * @return bool
     */
    public static function sendSystemNotice($user_id, $user_type, $title, $content, $relate_id = null, $image_ids = null, $url = null){
        DB::beginTransaction();
        if (!$message_id = DefService::addMessage(MessageEnum::SYSTEMNOTICE,$title,$content, $relate_id, $image_ids, $url)){
            DB::rollBack();
            return false;
        }
        $send_arr = [
            'user_id'       => $user_id,
            'user_type'     => $user_type,
            'message_id'    => $message_id,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        if (!MessageSendRepository::getAddId($send_arr)){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 发送公告
     * @param $user_type
     * @param $title
     * @param $content
     * @return bool
     */
    public static function sendAnnounce($user_type, $title, $content){
        DB::beginTransaction();
        if (!$message_id = DefService::addMessage(MessageEnum::ANNOUNCE,$title,$content)){
            DB::rollBack();
            return false;
        }
        $send_arr = [
            'user_id'       => 0,
            'user_type'     => $user_type,
            'message_id'    => $message_id,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        if (!MessageSendRepository::getAddId($send_arr)){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 发送通知
     * @param $user_id
     * @param $category
     * @param $title
     * @param $content
     * @param $relate_id
     * @param null $image_ids
     * @param null $url
     * @return bool
     */
    public static function sendMessage($user_id,$category, $title, $content, $relate_id = null, $image_ids = null, $url = null){
        DB::beginTransaction();
        if (!$message_id = DefService::addMessage($category,$title,$content, $relate_id, $image_ids, $url)){
            DB::rollBack();
            return false;
        }
        $send_arr = [
            'user_id'       => $user_id,
            'user_type'     => MessageEnum::MEMBER,
            'message_id'    => $message_id,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        if (!MessageSendRepository::getAddId($send_arr)){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

}
            