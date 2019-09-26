<?php


namespace App\Repositories;


use App\Models\ScoreSortModel;
use App\Repositories\Traits\RepositoryTrait;

class ScoreSortRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreSortModel $model)
    {
        $this->model = $model;
    }
}
            