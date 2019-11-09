<?php


namespace App\Repositories;


use App\Models\PrimeMerchantViewModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeMerchantViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantViewModel $model)
    {
        $this->model = $model;
    }
}
            