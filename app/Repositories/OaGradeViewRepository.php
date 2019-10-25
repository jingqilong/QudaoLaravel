<?php


namespace App\Repositories;


use App\Models\OaGradeCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class OaGradeViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * OaGradeViewRepository constructor.
     * @param OaGradeCollectModel $model
     */
    public function __construct(OaGradeCollectModel $model)
    {
        $this->model = $model;
    }
}