<?php


namespace App\Repositories;


use App\Models\MemberAddressModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberAddressRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberAddressModel $model)
    {
        $this->model = $model;
    }
}
            