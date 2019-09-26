<?php


namespace App\Repositories;


use App\Models\MemberTradesLogModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberTradesLogRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberTradesLogModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加交易日志
     * @param $trade_id
     * @param $amount
     * @param $title
     * @param $content
     * @return integer|null
     */
    protected function addLog($trade_id, $amount, $title, $content){
        return $this->getAddId([
            'trade_id'      => $trade_id,
            'amount'        => $amount,
            'title'         => $title,
            'content'       => $content,
            'created_at'    => time(),
        ]);
    }
}
            