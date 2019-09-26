<?php


namespace App\Repositories;


use App\Models\PrimeImagesModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeImagesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeImagesModel $model)
    {
        $this->model = $model;
    }
}
            