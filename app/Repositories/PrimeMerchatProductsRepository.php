<?php


namespace App\Repositories;


use App\Models\PrimeMerchatProductsModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeMerchatProductsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchatProductsModel $model)
    {
        $this->model = $model;
    }
}
            