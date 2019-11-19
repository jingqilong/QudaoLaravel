<?php


namespace App\Repositories;


use App\Models\ScoreRecordModel;
use App\Repositories\Traits\RepositoryTrait;

class ScoreRecordRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreRecordModel $model)
    {
        $this->model = $model;
    }
}
            