<?php


namespace App\Repositories;


use App\Models\MemberViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberViewModel $model)
    {
        $this->model = $model;
    }
}
            