<?php
namespace App\Services\Activity;


use App\Enums\ActivityCommentEnum;
use App\Repositories\ActivityCommentsRepository;
use App\Repositories\ActivityDetailRepository;
use App\Services\BaseService;
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
        $add_arr = [
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
     * 获取活动评论列表
     * @param $request
     * @return mixed
     */
    public function getActivityComment($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page'] ?? 20;
        $comment_where = [
            'activity_id'   => $request['activity_id'],
            'status'        => ActivityCommentEnum::PASS,
            'hidden'        => ActivityCommentEnum::DISPLAY,
            'deleted_at'    => 0
        ];
        $comment_column = ['id','content','comment_name','comment_avatar','member_id','created_at'];
        if (!$comment_list = ActivityCommentsRepository::getList($comment_where,$comment_column,'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        unset($comment_list['first_page_url'], $comment_list['from'],
            $comment_list['from'], $comment_list['last_page_url'],
            $comment_list['next_page_url'], $comment_list['path'],
            $comment_list['prev_page_url'], $comment_list['to']);
        if (empty($comment_list['data'])){
            $this->setMessage('暂无数据！');
            return $comment_list;
        }
        foreach ($comment_list['data'] as &$value){
            $value['created_at'] = date('Y-m-d H:i',$value['created_at']);
        }
        $this->setMessage('获取成功！');
        return $comment_list;
    }

    /**
     * 获取所有评论（后台）
     * @param $request
     * @return bool|null
     */
    public function getCommentList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$comment_list = ActivityCommentsRepository::getList(['id' => ['>',0]],['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        unset($comment_list['first_page_url'], $comment_list['from'],
            $comment_list['from'], $comment_list['last_page_url'],
            $comment_list['next_page_url'], $comment_list['path'],
            $comment_list['prev_page_url'], $comment_list['to']);
        if (empty($comment_list['data'])){
            $this->setMessage('暂无数据！');
            return $comment_list;
        }
        $activity_ids = array_column($comment_list['data'],'activity_id');
        $activity_list = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]]);
        foreach ($comment_list['data'] as &$value){
            $value['activity_name'] = '';
            if ($activity = $this->searchArray($activity_list,'id',$value['activity_id'])){
                $value['activity_name'] = reset($activity)['name'];
            }
            $value['created_at'] = date('Y-m-d H:i',$value['created_at']);
        }
        $this->setMessage('获取成功！');
        return $comment_list;
    }

    /**
     * 审核评论
     * @param $comment_id
     * @param $audit
     * @return bool
     */
    public function auditComment($comment_id, $audit)
    {
        if (!$comment = ActivityCommentsRepository::getOne(['id' => $comment_id])){
            $this->setError('评论不存在！');
            return false;
        }
        if ($comment['status'] > ActivityCommentEnum::PENDING){
            $this->setError('评论已审核！');
            return false;
        }
        $status = $audit == 1 ? ActivityCommentEnum::PASS : ActivityCommentEnum::NOPASS;
        if (!ActivityCommentsRepository::getUpdId(['id' => $comment_id],['status' => $status])){
            $this->setError('审核失败！');
            return false;
        }
        $this->setMessage('审核成功！');
        return true;
    }
}
            