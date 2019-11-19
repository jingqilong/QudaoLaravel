<?php


namespace App\Repositories;


use App\Enums\CommentsEnum;
use App\Models\CommonCommentsModel;
use App\Repositories\Traits\RepositoryTrait;

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
        $comment = $this->getOrderOne($where,'created_at','desc');
        return $comment;
    }
}
            