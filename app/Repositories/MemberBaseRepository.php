<?php


namespace App\Repositories;


use App\Models\MemberBaseModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberBaseRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberBaseModel $model)
    {
        $this->model = $model;
    }
}
            