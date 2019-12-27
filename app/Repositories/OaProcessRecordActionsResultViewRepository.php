<?php


namespace App\Repositories;


use App\Models\OaProcessRecordActionsResultViewModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessRecordActionsResultViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessRecordActionsResultViewModel $model)
    {
        $this->model = $model;
    }
}
            