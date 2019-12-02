<?php


namespace App\Repositories;


use App\Models\MemberOrdersViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberOrdersViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberOrdersViewModel $model)
    {
        $this->model = $model;
    }
}
            