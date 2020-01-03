<?php


namespace App\Repositories;


use App\Models\CommonUserSurveyModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonUserSurveyRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonUserSurveyModel $model)
    {
        $this->model = $model;
    }
}
            