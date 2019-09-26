<?php


namespace App\Repositories;


use App\Models\ShopAuditModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopAuditRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopAuditModel $model)
    {
        $this->model = $model;
    }
}
            