<?php
namespace App\Services\Common;


use App\Enums\CommentsEnum;
use App\Enums\FeedBacksEnum;
use App\Enums\MemberEnum;
use App\Repositories\CommonFeedBacksRepository;
use App\Repositories\CommonFeedBacksViewRepository;
use App\Repositories\CommonFeedbackThreadRepository;
use App\Repositories\MemberGradeDefineRepository;
use App\Repositories\MemberGradeRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class FeedBacksService extends BaseService
{
    use HelpTrait;
    public $auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }
    /**
     * 添加用户反馈
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
        if (!CommonFeedBacksRepository::getAddId($request_arr)){
            $this->setError('信息提交失败!');
            return false;
        }
        $this->setMessage('感谢您的反馈!');
        return true;
    }

    /**
     * oa 获取成员反馈
     * @param $request
     * @return bool|mixed|null
     */
    public function feedBackList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['keywords'] ?? null;
        $where      = ['id' => ['>',1]];
        if (!empty($keywords)){
            $keyword   = [$keywords => ['ch_name','mobile','content']];
            if (!$list = CommonFeedBacksViewRepository::search($keyword,$where,['*'],$page,$page_num,'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = CommonFeedBacksViewRepository::getList($where,['*'],'id','desc',$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $list['data'] = MemberGradeRepository::bulkHasManyWalk(
            $list['data'],
            ['from' => 'member_id','to' => 'user_id'],
            ['user_id','grade'],
            [],
            function($src_item,$member_grade_items){
                $grade = Arr::only($member_grade_items[$src_item['member_id']],'grade');
                $src_item['grade'] = MemberGradeDefineRepository::getLabelById((int)$grade,'普通成员');
                return $src_item;
            }
        );
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 客户服务 回复反馈消息
     * @param $request
     * @return bool
     */
    public function addCallBackFeedBack($request)
    {
        $request_arr = Arr::only($request,['replay_id','feedback_id','content']);
        if (!CommonFeedbackThreadRepository::exists(['id' => $request_arr['replay_id']]) || !CommonFeedBacksRepository::exists(['id' => $request_arr['feedback_id']])){
            $this->setError('没有反馈消息!');
            return false;
        }
        if (CommonFeedbackThreadRepository::exists($request_arr)){
            $this->setError('这条信息您已经回复过了哦!');
            return false;
        }
        $request_arr['operator_type'] = FeedBacksEnum::OA;
        $request_arr['status']        = FeedBacksEnum::MANAGE;
        $request_arr['created_at']    = time();
        $request_arr['created_by']    = time();
        if (!CommonFeedbackThreadRepository::getAddId($request_arr)){
            $this->setError('回复失败!');;
            return false;
        }
        $this->setMessage('回复成功!');
        return true;
    }

    /**
     * 获取OA反馈的回复详情
     * @param $request
     * @return bool|null
     */
    public function getCallBackFeedBack($request)
    {
        if (!$list = CommonFeedbackThreadRepository::getList(['feedback_id' => $request['feedback_id']])){
            $this->setError('获取失败!');;
            return false;
        }
        foreach ($list as &$value){
            $value['status_title']          = FeedBacksEnum::getStatus($value['status']);
            $value['operator_type_title']   = FeedBacksEnum::getOperatorType($value['operator_type']);
        }
        $this->setMessage('获取成功!');
        return $list;
    }
}