<?php


namespace App\Repositories;


use App\Models\MemberPersonalServiceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPersonalServiceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberPersonalServiceModel $model)
    {
        $this->model = $model;
    }
}
            