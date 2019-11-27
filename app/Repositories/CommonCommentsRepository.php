<?php


namespace App\Repositories;


use App\Enums\CommentsEnum;
use App\Models\CommonCommentsModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;

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
        $check = ['id','content','comment_name','comment_avatar','image_ids','member_id','created_at'];
        if (!$comment = $this->getOrderOne($where,'created_at','desc',$check)){
            return [];
        }
        $comment = ImagesService::getOneImagesConcise($comment,['image_ids' => 'several']);
        unset($comment['image_ids']);
        return $comment;
    }
}
            