<?php
namespace App\Repositories;


use App\Models\ShopInventoryModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopInventoryRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopInventoryModel $model)
    {
        $this->model = $model;
    }
}
