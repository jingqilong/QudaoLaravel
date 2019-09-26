<?php


namespace App\Repositories;


use App\Models\MemberServiceRecordModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberServiceRecordRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberServiceRecordModel $model)
    {
        $this->model = $model;
    }
}
            