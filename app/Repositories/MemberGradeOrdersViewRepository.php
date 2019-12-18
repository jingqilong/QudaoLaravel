<?php


namespace App\Repositories;


use App\Models\MemberGradeOrdersViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeOrdersViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeOrdersViewModel $model)
    {
        $this->model = $model;
    }
}
            