<?php


namespace App\Repositories;


use App\Models\ShopGoodSpecListViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodSpecListViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodSpecListViewModel $model)
    {
        $this->model = $model;
    }
}
            