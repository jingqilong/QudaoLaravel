<?php


namespace App\Repositories;


use App\Models\MemberGradeModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeModel $model)
    {
        $this->model = $model;
    }
}
            