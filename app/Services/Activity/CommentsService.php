<?php
namespace App\Services\Activity;


use App\Enums\ActivityRegisterEnum;
use App\Repositories\ActivityCommentKeywordsRepository;
use App\Repositories\ActivityCommentsRepository;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\MemberGradeRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class CommentsService extends BaseService
{
    use HelpTrait;
    public $auth;

    /**
     * CommentsService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 会员评论活动
     * @param $request
     * @return bool
     */
    public function comment($request)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        if ($activity['start_time'] > time()){
            $this->setError('当前活动还未开始，不能评论！');
            return false;
        }
        if ($activity['end_time'] > time()){
            $this->setError('当前活动还未结束，不能评论！');
            return false;
        }
        $member = $this->auth->user();
        if (ActivityCommentsRepository::exists(['activity_id' => $request['activity_id'],'member_id' => $member->m_id,'deleted_at' => 0])){
            $this->setError('您已经评论过了，无法再次评论！');
            return false;
        }
        //TODO 此处判断会员有没有报名
        $keyword_ids = explode(',',$request['keyword_ids']);
        if (count($keyword_ids) != ActivityCommentKeywordsRepository::count(['id' => ['in', $keyword_ids]])){
            $this->setError('存在无效的评论关键字！');
            return false;
        }
        $add_arr = [
            'score'         => $request['score'],
            'keyword_ids'   => $request['keyword_ids'],
            'content'       => $request['content'] ?? '',
            'comment_name'  => $request['comment_name'] ?? $member->m_cname,
            'comment_avatar'=> $request['comment_avatar'] ?? $member->m_img,
            'activity_id'   => $request['activity_id'],
            'member_id'     => $member->m_id,
            'created_at'    => time()
        ];
        if (ActivityCommentsRepository::getAddId($add_arr)){
            $this->setMessage('评论成功！');
            return true;
        }
        $this->setError('评论失败！');
        return false;
    }

    /**
     * 会员删除自己的评论
     * @param $id
     * @return bool
     */
    public function deleteComment($id)
    {
        if (!$comment = ActivityCommentsRepository::getOne(['id' => $id])){
            $this->setError('该评论不存在！');
            return false;
        }
        if ($comment['deleted_at'] > 0){
            $this->setError('该评论已删除！');
            return false;
        }
        $member = $this->auth->user();
        if ($comment['member_id'] != $member->m_id){
            $this->setError('只能删除自己的评论！');
            return false;
        }
        if (ActivityCommentsRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 活动报名
     * @param $request
     * @return bool
     */
    public function register($request)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $member = $this->auth->user();
        if (!$grade = MemberGradeRepository::getOne(['user_id' => $member])){
            $this->setError('您还不是会员，无法参加活动！');
            return false;
        }
        $time = time();
        if ($time > $activity['start_time'] && $time < $activity['end_time']){
            $this->setError('活动已经开始，无法进行报名了！');
            return false;
        }
        if ($activity['end_time'] < $time){
            $this->setError('活动已经结束了，下次再来吧！');
            return false;
        }
        if (ActivityRegisterRepository::exists([
            'activity_id' => $request['activity_id'],
            'member_id' => $member->m_id,
            'status' => ['<',5]])){
            $this->setError('您已经报过名了，请勿重复报名！');
            return false;
        }
        //计算会员价格
        $member_price   = $this->discount($grade['grade'],$activity['price']);
        $add_arr = [
            'activity_id'   => $request['activity_id'],
            'member_id'     => $member->m_id,
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'activity_price'=> $activity['price'],
            'member_price'  => $member_price,
            'status'        => ActivityRegisterEnum::PENDING,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (ActivityRegisterRepository::getAddId($add_arr)){
            //TODO 此处可以添加报名后发通知的事务
            #发送短信
            if (!empty($member->m_phone)){
                $sms = new SmsService();
                $content = '您好！欢迎参加活动《'.$activity['name'].'》,我们将在24小时内受理您的报名申请，如有疑问请联系客服：000-00000！';
                $sms->sendContent($member->m_phone,$content);
            }
            $this->setMessage('报名成功！');
            return true;
        }
        $this->setError('报名失败！');
        return false;
    }
}
            