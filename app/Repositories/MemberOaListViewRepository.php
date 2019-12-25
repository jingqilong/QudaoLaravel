<?php


namespace App\Repositories;


use App\Models\MemberOaListViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberOaListViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberOaListViewModel $model)
    {
        $this->model = $model;
    }
}
            