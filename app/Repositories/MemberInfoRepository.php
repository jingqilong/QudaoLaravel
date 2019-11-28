<?php


namespace App\Repositories;


use App\Models\MemberInfoModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberInfoRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberInfoModel $model)
    {
        $this->model = $model;
    }
}
            