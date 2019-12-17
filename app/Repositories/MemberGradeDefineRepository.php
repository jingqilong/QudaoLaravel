<?php


namespace App\Repositories;


use App\Models\MemberGradeDefineModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeDefineRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeDefineModel $model)
    {
        $this->model = $model;
    }
}
            