<?php


namespace App\Repositories;


use App\Models\OaProcessCategoriesModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessCategoriesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessCategoriesModel $model)
    {
        $this->model = $model;
    }
}
            