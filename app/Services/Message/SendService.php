<?php
namespace App\Services\Message;


use App\Enums\MessageEnum;
use App\Repositories\MemberRepository;
use App\Repositories\MessageSendRepository;
use App\Repositories\MessageSendViewRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;

class SendService extends BaseService
{
    use HelpTrait;
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

    /**
     * 获取所有已发送消息的列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getAllMessageList($request)
    {
        $page               = $request['page'] ?? 1;
        $page_num           = $request['page_num'] ?? 20;
        $user_type          = $request['user_type'] ?? null;
        $message_category   = $request['message_category'] ?? null;
        $where              = ['id' => ['<>',0]];
        if (!is_null($user_type)){
            $where['user_type'] = $user_type;
        }
        if (!is_null($message_category)){
            $where['category_id'] = $message_category;
        }
        if (!$list = MessageSendViewRepository::getList($where,['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        #处理图片
        $list['data'] = ImagesService::getListImages($list['data'],['image_ids' => 'several']);
        #获取相关会员列表
        $member_list = [];
        if ($member_message_list = $this->searchArray($list['data'],'user_type',MessageEnum::MEMBER)){
            $member_ids = array_column($member_message_list,'user_id');
            $member_list = MemberRepository::getList(['m_id' => ['in',$member_ids]],['m_id','m_cname','m_phone']);
        }
        #获取相关商户列表
        $merchant_list = [];
        if ($merchant_message_list = $this->searchArray($list['data'],'user_type',MessageEnum::MERCHANT)){
            $merchant_ids = array_column($merchant_message_list,'user_id');
            $merchant_list = PrimeMerchantRepository::getList(['id' => ['in',$merchant_ids]],['id','name','mobile']);
        }
        #获取相关OA员工列表
        $oa_list = [];
        if ($oa_message_list = $this->searchArray($list['data'],'user_type',MessageEnum::OAEMPLOYEES)){
            $oa_ids = array_column($oa_message_list,'user_id');
            $oa_list = OaEmployeeRepository::getList(['id' => ['in',$oa_ids]],['id','real_name','mobile']);
        }
        foreach ($list['data'] as &$value){
            #匹配会员信息
            if ($value['user_type'] == MessageEnum::MEMBER){
                $value['user_name']     = '会员公告';
                $value['user_mobile']   = '--';
                if ($member = $this->searchArray($member_list,'m_id',$value['user_id'])){
                    $member = reset($member);
                    $value['user_name']     = $member['m_cname'];
                    $value['user_mobile']   = $member['m_phone'];
                }
            }
            #匹配商户信息
            if ($value['user_type'] == MessageEnum::MERCHANT){
                $value['user_name']     = '商户公告';
                $value['user_mobile']   = '--';
                if ($merchant = $this->searchArray($merchant_list,'id',$value['user_id'])){
                    $merchant = reset($merchant);
                    $value['user_name']     = $merchant['name'];
                    $value['user_mobile']   = $merchant['mobile'];
                }
            }
            #匹配OA员工信息
            if ($value['user_type'] == MessageEnum::OAEMPLOYEES){
                $value['user_name']     = 'OA员工公告';
                $value['user_mobile']   = '--';
                if ($oa = $this->searchArray($oa_list,'id',$value['user_id'])){
                    $oa = reset($oa);
                    $value['user_name']     = $oa['real_name'];
                    $value['user_mobile']   = $oa['mobile'];
                }
            }
            $value['delete'] = empty($value['deleted_at']) ? 0 : 1;
            unset($value['deleted_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 后台发送系统消息
     * @param $request
     * @return bool
     */
    public function sendSystem($request)
    {
        #匹配会员信息
        if ($request['user_type'] == MessageEnum::MEMBER){
            if (!MemberRepository::exists(['m_id' => $request['user_id']])){
                $this->setError('会员信息不存在！');
            }
        }
        #匹配商户信息
        if ($request['user_type'] == MessageEnum::MERCHANT){
            if (!PrimeMerchantRepository::exists(['id' => $request['user_id']])){
                $this->setError('商户信息不存在！');
            }
        }
        #匹配OA员工信息
        if ($request['user_type'] == MessageEnum::OAEMPLOYEES){
            if (!OaEmployeeRepository::exists(['id' => $request['user_id']])){
                $this->setError('OA员工信息不存在！');
            }
        }
        if (!self::sendSystemNotice($request['user_id'],$request['user_type'],$request['title'],$request['content'],null,$request['image_ids'] ?? null,$request['url'] ?? null)){
            $this->setError('发送失败！');
            return false;
        }
        $this->setMessage('发送成功！');
        return true;
    }

}
            