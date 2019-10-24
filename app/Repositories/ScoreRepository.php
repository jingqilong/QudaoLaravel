<?php


namespace App\Repositories;


use App\Models\ScoreModel;
use App\Repositories\Traits\RepositoryTrait;

class ScoreRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreModel $model)
    {
        $this->model = $model;
    }
}
            