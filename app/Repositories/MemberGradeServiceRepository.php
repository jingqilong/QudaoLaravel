<?php


namespace App\Repositories;


use App\Models\MemberGradeServiceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeServiceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeServiceModel $model)
    {
        $this->model = $model;
    }
}
            