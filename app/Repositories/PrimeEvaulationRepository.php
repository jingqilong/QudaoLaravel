<?php


namespace App\Repositories;


use App\Models\PrimeEvaulationModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeEvaulationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeEvaulationModel $model)
    {
        $this->model = $model;
    }
}
            