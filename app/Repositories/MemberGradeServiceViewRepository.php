<?php


namespace App\Repositories;


use App\Models\MemberGradeServiceViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeServiceViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeServiceViewModel $model)
    {
        $this->model = $model;
    }
}
            