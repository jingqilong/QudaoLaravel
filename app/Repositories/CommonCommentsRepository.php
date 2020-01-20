<?php


namespace App\Repositories;


use App\Enums\CommentsEnum;
use App\Models\CommonCommentsModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use Illuminate\Support\Facades\Auth;

class CommonCommentsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonCommentsModel $model)
    {
        $this->model = $model;
    }

    protected function getOneComment($goods_id,$type,$status=CommentsEnum::PASS)
    {
        $where = [
            'related_id'    => $goods_id,
            'status'        => $status,
            'type'          => $type,
            'hidden'        => CommentsEnum::ACTIVITE,
            'deleted_at'    => 0
        ];
        $check = ['id','content','comment_name','comment_avatar','image_ids','created_at'];
        if (!$comment = $this->getOrderOne($where,'created_at','desc',$check)){
            return [];
        }
        $comment = ImagesService::getOneImagesConcise($comment,['comment_avatar' => 'single']);
        $comment['created_at'] = date('Y-m-d',strtotime($comment['created_at']));
        unset($comment['image_ids'],$comment['comment_avatar']);
        return $comment;
    }

    /**
     * 添加评论
     * @param $content
     * @param $type
     * @param $order_related_id
     * @param $relate_id
     * @param string $image_ids
     * @return mixed
     */
    protected function addComment($content, $type, $order_related_id, $relate_id, $image_ids = ''){
        $member = Auth::guard('member_id')->user();
        $add_arr = [
            'member_id'         => $member->id,
            'content'           => $content,
            'comment_name'      => $member->ch_name,
            'comment_avatar'    => $member->avatar_id,
            'type'              => $type,
            'order_related_id'  => $order_related_id,
            'related_id'        => $relate_id,
            'image_ids'         => $image_ids,
            'status'            => CommentsEnum::SUBMIT,
            'hidden'            => CommentsEnum::HIDDEN,
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
        return $this->getAddId($add_arr);
    }
}
            