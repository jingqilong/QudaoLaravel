<?php


namespace App\Repositories;


use App\Models\MemberSignModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberSignRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberSignModel $model)
    {
        $this->model = $model;
    }
}
            