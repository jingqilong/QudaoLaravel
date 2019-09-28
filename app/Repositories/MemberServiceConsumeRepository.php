<?php


namespace App\Repositories;


use App\Models\MemberServiceConsumeModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberServiceConsumeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberServiceConsumeModel $model)
    {
        $this->model = $model;
    }
}
            