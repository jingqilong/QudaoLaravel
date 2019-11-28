<?php


namespace App\Repositories;


use App\Models\ScoreRecordViewModel;
use App\Repositories\Traits\RepositoryTrait;

class ScoreRecordViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreRecordViewModel $model)
    {
        $this->model = $model;
    }
}
            