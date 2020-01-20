<?php
namespace App\Services\Common;


use App\Enums\FeedBacksEnum;
use App\Repositories\CommonFeedBacksRepository;
use App\Repositories\CommonFeedBacksViewRepository;
use App\Repositories\CommonFeedbackThreadRepository;
use App\Repositories\MemberGradeDefineRepository;
use App\Repositories\MemberOaListViewRepository;
use App\Services\BaseService;
use App\Services\Message\MessageCacheService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeedBacksService extends BaseService
{
    use HelpTrait;
    public $auth;
    public $oa_auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth     = Auth::guard('member_api');
        $this->oa_auth  = Auth::guard('oa_api');
    }
    /**
     * 用户添加反馈
     * @param $request
     * @return bool
     */
    public function addFeedBack($request)
    {
        $member      = $this->auth->user();
        $request_arr = Arr::only($request,['content','mobile']);
        $request_arr['member_id'] = $member->id;
        $request_arr['mobile'] = empty($request_arr['mobile']) ? $member->mobile : $request_arr['mobile'];
        if (CommonFeedBacksRepository::exists($request_arr)){
            $this->setError('您的信息已提交!');
            return false;
        }
        $request_arr['created_at'] = time();
        DB::beginTransaction();
        if (!$id = CommonFeedBacksRepository::getAddId($request_arr)){
            $this->setError('信息提交失败!');
            DB::rollBack();
            return false;
        }
        $feedback_thread_arr = [
            'feedback_id'   => $id,
            'replay_id'     => $id,
            'content'       => $request_arr['content'],
            'status'        => FeedBacksEnum::SUBMIT,
            'operator_type' => FeedBacksEnum::MEMBER,
            'created_at'    => time(),
            'created_by'    => $member->id,
        ];
        if (!CommonFeedbackThreadRepository::getAddId($feedback_thread_arr)){
            $this->setError('信息提交失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('感谢您的反馈!');
        return true;
    }

    /**
     * oa 获取成员反馈列表
     * @param $request
     * @return bool|mixed|null
     */
    public function feedBackList($request)
    {
        $keywords   = $request['keywords'] ?? null;
        $where      = ['id' => ['>',0]];
        if (!empty($keywords)){
            $keyword   = [$keywords => ['ch_name','mobile','content']];
            if (!$list = CommonFeedBacksViewRepository::search($keyword,$where,['*'],'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = CommonFeedBacksViewRepository::getList($where,['*'],'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }

        $list['data'] = MemberOaListViewRepository::bulkHasManyWalk(
            $list['data'],
            ['from' => 'member_id','to' => 'id'],
            ['id','grade'],
            [],
            function($src_item,$member_grade_items){
                foreach ($member_grade_items as &$value){
                    $src_item['grade'] = MemberGradeDefineRepository::getLabelById((int)$value['grade'],'普通成员');
                }
                return $src_item;
            }
        );
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 用户回复员工
     * @param $request
     * @return bool
     */
    public function callBackEmployee($request)
    {
        $member = $this->auth->user();
        $request_arr = Arr::only($request,['replay_id','feedback_id','content']);
        if (!$replay_feedback = CommonFeedbackThreadRepository::getOne(['id' => $request_arr['replay_id']])){
            $this->setError('没有反馈消息!');
            return false;
        }
        if (!CommonFeedBacksRepository::exists(['id' => $request_arr['feedback_id']])){
            $this->setError('没有反馈消息!');
            return false;
        }
        if (CommonFeedbackThreadRepository::exists($request_arr)){
            $this->setError('这条信息您已经回复过了哦!');
            return false;
        }
        $request_arr['operator_type'] = FeedBacksEnum::MEMBER;
        $request_arr['status']        = FeedBacksEnum::SUBMIT;
        $request_arr['created_at']    = time();
        $request_arr['created_by']    = $member->id;
        if (!CommonFeedbackThreadRepository::getAddId($request_arr)){
            $this->setError('回复失败!');
            return false;
        }
        MessageCacheService::increaseCacheFeedbackMessage($replay_feedback['created_by'],3,$request_arr['feedback_id'],$request_arr['replay_id']);
        $this->setMessage('回复成功!');
        return true;
    }



    /**
     * 员工回复用户
     * @param $request
     * @return bool
     */
    public function addCallBackFeedBack($request)
    {
        $employee = $this->oa_auth->user();
        $request_arr = Arr::only($request,['replay_id','feedback_id','content']);
        if (!$replay_feedback = CommonFeedbackThreadRepository::getOne(['id' => $request_arr['replay_id']])){
            $this->setError('没有反馈消息!');
            return false;
        }
        if (!CommonFeedBacksRepository::exists(['id' => $request_arr['feedback_id']])){
            $this->setError('没有反馈消息!');
            return false;
        }
        $request_arr['operator_type'] = FeedBacksEnum::OA;
        $request_arr['status']        = FeedBacksEnum::MANAGE;
        $request_arr['created_at']    = time();
        $request_arr['created_by']    = $employee->id;
        DB::beginTransaction();
        if (!CommonFeedbackThreadRepository::getAddId($request_arr)){
            $this->setError('回复失败!');
            DB::rollBack();
            return false;
        }
        if (!CommonFeedBacksRepository::getUpdId(['id' => $request_arr['feedback_id']],['status' => FeedBacksEnum::MANAGE])){
            $this->setError('回复失败!');
            DB::rollBack();
            return false;
        }
        MessageCacheService::increaseCacheFeedbackMessage($replay_feedback['created_by'],1,$request_arr['feedback_id'],$request_arr['replay_id']);
        $this->setMessage('回复成功!');
        DB::commit();
        return true;
    }

    /**
     * OA反馈的回复详情
     * @param $request
     * @return mixed
     */
    public function getCallBackFeedBack($request)
    {
        $employee = Auth::guard('oa_api')->user();
        $column   = ['id','member_id','content','created_at'];
        if (!$call_back_info = CommonFeedBacksViewRepository::getOne(['id' => $request['feedback_id']],$column)){
            $this->setError('获取失败!');
            return false;
        }
        MessageCacheService::cacheFeedbackId($employee->id,3,$request['feedback_id']);
        $list_where  = ['feedback_id' => $call_back_info['id']];
        $list_column = ['id','content','status','operator_type','created_at','created_by'];
        if (!$call_back_list = CommonFeedbackThreadRepository::getAllList($list_where,$list_column,'created_at','asc')){
            $this->setError('没有反馈消息!');
            return false;
        }
        $member_ids = [];
        Arr::where($call_back_list,function (&$value) use (&$member_ids){
            if ($value['operator_type'] == FeedBacksEnum::MEMBER){
                $member_ids[] = $value['created_by'];
                return $value;
            }
        });
        $member_list = MemberOaListViewRepository::getAllList(['id' => ['in',$member_ids]],['id','img_url']);
        $member_list = createArrayIndex($member_list,'id');
        $default_avatar = url('images/service_default_avatar.jpeg');
        foreach ($call_back_list as &$value){
            $value['avatar_url'] = ($value['operator_type'] == FeedBacksEnum::MEMBER) ? ($member_list[$value['created_by']]['img_url'] ?? $default_avatar) : $default_avatar;
            unset($value['created_by']);
        }
        MessageCacheService::clearCacheFeedbackMessage($employee->id,3,$request['feedback_id']);
        $this->setMessage('获取成功!');
        return ['member_call_back' => $call_back_info,'oa_call_back' => $call_back_list];
    }

    /**
     * 用户获取反馈列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getCallBackList($request)
    {
        $member   = $this->auth->user();
        $status   = $request['status'] ?? null;
        $where    = ['member_id' => $member->id];
        if (empty($status)) $where['status'] = ['<', FeedBacksEnum::CLOSE]; else $where['status'] = $request['status'];
        $column   = ['id','member_id','content','img_url','created_at'];
        if (!$list = CommonFeedBacksViewRepository::getList($where,$column,'id','desc')){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 用户获取反馈详情
     * @param $request
     * @return array|bool|mixed|null
     */
    public function getBackFeedBackList($request)
    {
        $member   = $this->auth->user();
        $column   = ['id','member_id','content','created_at'];
        if (!$call_back_info = CommonFeedBacksViewRepository::getOne(['member_id' => $member->id,'id' => $request['feedback_id']],$column)){
            $this->setError('没有反馈消息!');
            return false;
        }
        MessageCacheService::cacheFeedbackId($member->id,1,$request['feedback_id']);
        $list_where  = ['feedback_id' => $call_back_info['id']];
        $list_column = ['id','content','status','operator_type','created_at','created_by'];
        if (!$call_back_list = CommonFeedbackThreadRepository::getAllList($list_where,$list_column,'created_at','asc')){
            $this->setError('暂时没有回复消息!');
            return false;
        }
        $member_ids = [];
        Arr::where($call_back_list,function (&$value) use (&$member_ids){
            if ($value['operator_type'] == FeedBacksEnum::MEMBER){
                $member_ids[] = $value['created_by'];
                return $value;
            }
        });
        $member_list = MemberOaListViewRepository::getAllList(['id' => ['in',$member_ids]],['id','img_url']);
        $member_list = createArrayIndex($member_list,'id');
        $default_avatar = url('images/service_default_avatar.jpeg');
        foreach ($call_back_list as &$value){
            $value['avatar_url'] = ($value['operator_type'] == FeedBacksEnum::MEMBER) ? ($member_list[$value['created_by']]['img_url'] ?? $default_avatar) : $default_avatar;
            unset($value['created_by']);
        }
        MessageCacheService::clearCacheFeedbackMessage($member->id,1,$request['feedback_id']);
        $this->setMessage('获取成功!');
        return ['member_call_back' => $call_back_info,'oa_call_back' => $call_back_list];
    }

}