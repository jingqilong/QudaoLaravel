<?php


namespace App\Repositories;


use App\Models\ScoreCategoryModel;
use App\Repositories\Traits\RepositoryTrait;

class ScoreCategoryRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreCategoryModel $model)
    {
        $this->model = $model;
    }
}
            