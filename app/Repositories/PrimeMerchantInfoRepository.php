<?php


namespace App\Repositories;


use App\Models\PrimeMerchantInfoModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeMerchantInfoRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantInfoModel $model)
    {
        $this->model = $model;
    }
}
            