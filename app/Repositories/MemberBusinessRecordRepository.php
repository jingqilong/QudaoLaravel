<?php


namespace App\Repositories;


use App\Models\MemberBusinessRecordModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberBusinessRecordRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberBusinessRecordModel $model)
    {
        $this->model = $model;
    }
}
            