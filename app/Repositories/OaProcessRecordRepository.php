<?php


namespace App\Repositories;


use App\Models\OaProcessRecordModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessRecordRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessRecordModel $model)
    {
        $this->model = $model;
    }
}
            