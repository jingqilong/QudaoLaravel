<?php


namespace App\Repositories;


use App\Models\ShopOrderRelateModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;

class ShopOrderRelateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderRelateModel $model)
    {
        $this->model = $model;
    }
    /**
     * 获取评论详情
     * @param $related_id
     * @return array|string|null
     */
    protected function getCommentDetails($related_id)
    {
        $where = ['id' => $related_id,'deleted_at' => 0];
        if (!$comment_info = CommonCommentsRepository::getOne($where)){
            return false;
        }
        $comment_info['spec_value'] = ShopOrderGoodsRepository::getField(['id' => $comment_info['order_related_id']],'spec_relate_value');
        $comment_info = ImagesService::getOneImagesConcise($comment_info,['comment_avatar' => 'single']);
        $comment_info = ImagesService::getOneImagesConcise($comment_info,['image_ids' => 'several']);
        unset($comment_info['deleted_at'],$comment_info['status'],$comment_info['type'],$comment_info['order_related_id'],$comment_info['order_related_id']);
        return $comment_info;
    }
}
            