<?php


namespace App\Repositories;


use App\Models\OaProcessActionEventModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionEventRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionEventModel $model)
    {
        $this->model = $model;
    }
}
            