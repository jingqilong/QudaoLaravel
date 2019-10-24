<?php


namespace App\Repositories;


use App\Models\MemberGradeViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeViewModel $model)
    {
        $this->model = $model;
    }
}
            