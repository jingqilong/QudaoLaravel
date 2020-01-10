<?php


namespace App\Repositories;


use App\Models\MemberTradeListViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberTradeListViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * CommonFeedBacksModel constructor.
     * @param $model
     */
    public function __construct(MemberTradeListViewModel $model)
    {
        $this->model = $model;
    }
}
            