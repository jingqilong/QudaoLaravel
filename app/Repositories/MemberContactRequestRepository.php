<?php


namespace App\Repositories;


use App\Models\MemberContactRequestModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberContactRequestRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberContactRequestModel $model)
    {
        $this->model = $model;
    }
}
            