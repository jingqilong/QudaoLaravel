<?php


namespace App\Repositories;


use App\Models\PrimeMerchantProductsModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeMerchantProductsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantProductsModel $model)
    {
        $this->model = $model;
    }
}
            