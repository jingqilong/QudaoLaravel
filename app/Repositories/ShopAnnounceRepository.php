<?php


namespace App\Repositories;


use App\Models\ShopAnnounceModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopAnnounceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopAnnounceModel $model)
    {
        $this->model = $model;
    }
}
            