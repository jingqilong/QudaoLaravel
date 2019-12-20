<?php


namespace App\Repositories;


use App\Models\MemberGradeDetailViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeDetailViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeDetailViewModel $model)
    {
        $this->model = $model;
    }
}
            