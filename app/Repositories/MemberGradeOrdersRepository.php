<?php


namespace App\Repositories;


use App\Models\MemberGradeOrdersModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeOrdersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeOrdersModel $model)
    {
        $this->model = $model;
    }
}
            