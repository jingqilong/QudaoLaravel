<?php


namespace App\Repositories;


use App\Models\MemberCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberCollectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberCollectModel $model)
    {
        $this->model = $model;
    }
}
            